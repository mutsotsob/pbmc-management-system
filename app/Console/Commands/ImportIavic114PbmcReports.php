<?php

namespace App\Console\Commands;

use App\Models\Iavic114PbmcReport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SimpleXMLElement;
use Throwable;
use ZipArchive;

class ImportIavic114PbmcReports extends Command
{
    protected $signature = 'iavic114:import
                            {path : Absolute path to the Excel workbook}
                            {--sheet=Sheet1 : Worksheet name to import}
                            {--truncate : Delete existing imported rows before loading the sheet}';

    protected $description = 'Import IAVIC114 PBMC reports from an Excel workbook into the database';

    /**
     * Built-in Excel number formats commonly used for dates/times.
     *
     * @var array<int, string>
     */
    private const BUILTIN_FORMAT_TYPES = [
        14 => 'date',
        15 => 'date',
        16 => 'date',
        17 => 'date',
        18 => 'time',
        19 => 'time',
        20 => 'time',
        21 => 'time',
        22 => 'datetime',
        45 => 'time',
        46 => 'time',
        47 => 'time',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->argument('path');
        $sheetName = $this->option('sheet');

        if (!is_file($path)) {
            $this->error("Workbook not found: {$path}");
            return self::FAILURE;
        }

        try {
            [$rows, $sharedStrings, $styles] = $this->openWorkbookData($path, $sheetName);
            $headers = null;
            $imported = 0;
            $updated = 0;
            $skipped = 0;
            $sourceWorkbook = basename($path);

            if ($this->option('truncate')) {
                Iavic114PbmcReport::query()
                    ->where('source_workbook', $sourceWorkbook)
                    ->where('source_sheet', $sheetName)
                    ->delete();
            }

            foreach ($rows as $rowNumber => $row) {
                if ($headers === null) {
                    $headers = $this->extractHeaders($row, $sharedStrings, $styles);
                    continue;
                }

                $mapped = $this->mapRow($headers, $row, $sharedStrings, $styles);

                if (!$this->isImportableRow($mapped)) {
                    $skipped++;
                    continue;
                }

                $payload = $this->transformRow($mapped, $sourceWorkbook, $sheetName, $rowNumber);

                DB::transaction(function () use ($payload, &$imported, &$updated): void {
                    $existing = Iavic114PbmcReport::query()->where([
                        'source_workbook' => $payload['source_workbook'],
                        'source_sheet' => $payload['source_sheet'],
                        'source_row_number' => $payload['source_row_number'],
                    ])->first();

                    if ($existing) {
                        $existing->fill($payload)->save();
                        $updated++;
                        return;
                    }

                    Iavic114PbmcReport::create($payload);
                    $imported++;
                });
            }

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Imported', $imported],
                    ['Updated', $updated],
                    ['Skipped', $skipped],
                    ['Total processed', $imported + $updated + $skipped],
                ]
            );

            $this->info("Imported worksheet '{$sheetName}' from {$sourceWorkbook}.");
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * @return array{0: array<int, array<string, array{value:mixed,type:?string,style:int|null}>>, 1: array<int, string>, 2: array<int, string|null>}
     */
    private function openWorkbookData(string $path, string $sheetName): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Unable to open workbook: {$path}");
        }

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relationsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        $stylesXml = $zip->getFromName('xl/styles.xml');

        if ($workbookXml === false || $relationsXml === false) {
            $zip->close();
            throw new RuntimeException('Workbook structure is invalid.');
        }

        $sheetPath = $this->resolveSheetPath($workbookXml, $relationsXml, $sheetName);
        $sheetXml = $zip->getFromName($sheetPath);

        if ($sheetXml === false) {
            $zip->close();
            throw new RuntimeException("Worksheet '{$sheetName}' was not found in the workbook.");
        }

        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $sharedStrings = $sharedStringsXml === false ? [] : $this->parseSharedStrings($sharedStringsXml);
        $styles = $stylesXml === false ? [] : $this->parseStyles($stylesXml);
        $rows = $this->parseSheetRows($sheetXml, $sharedStrings, $styles);

        $zip->close();

        return [$rows, $sharedStrings, $styles];
    }

    private function resolveSheetPath(string $workbookXml, string $relationsXml, string $sheetName): string
    {
        $workbook = new SimpleXMLElement($workbookXml);
        $workbook->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

        $relations = new SimpleXMLElement($relationsXml);
        $relations->registerXPathNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $relationshipTargets = [];
        foreach ($relations->Relationship as $relationship) {
            $relationshipTargets[(string) $relationship['Id']] = 'xl/' . ltrim((string) $relationship['Target'], '/');
        }

        foreach ($workbook->xpath('//main:sheets/main:sheet') as $sheet) {
            if ((string) $sheet['name'] !== $sheetName) {
                continue;
            }

            $relationId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];

            if (!isset($relationshipTargets[$relationId])) {
                break;
            }

            return $relationshipTargets[$relationId];
        }

        throw new RuntimeException("Worksheet '{$sheetName}' is not defined in the workbook.");
    }

    /**
     * @return array<int, string>
     */
    private function parseSharedStrings(string $xml): array
    {
        $document = new SimpleXMLElement($xml);
        $document->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $strings = [];

        foreach ($document->xpath('//main:si') as $item) {
            $parts = $item->xpath('.//*[local-name()="t"]');
            $strings[] = implode('', array_map(static fn ($node) => (string) $node, $parts));
        }

        return $strings;
    }

    /**
     * @return array<int, string|null>
     */
    private function parseStyles(string $xml): array
    {
        $document = new SimpleXMLElement($xml);
        $document->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $customNumFmtTypes = [];
        foreach ($document->xpath('//main:numFmts/main:numFmt') as $numFmt) {
            $numFmtId = (int) $numFmt['numFmtId'];
            $customNumFmtTypes[$numFmtId] = $this->detectFormatType((string) $numFmt['formatCode']);
        }

        $styleTypes = [];
        foreach ($document->xpath('//main:cellXfs/main:xf') as $index => $xf) {
            $numFmtId = (int) $xf['numFmtId'];
            $styleTypes[$index] = self::BUILTIN_FORMAT_TYPES[$numFmtId] ?? $customNumFmtTypes[$numFmtId] ?? null;
        }

        return $styleTypes;
    }

    private function detectFormatType(string $formatCode): ?string
    {
        $normalized = strtolower(preg_replace('/\[[^\]]+\]/', '', $formatCode) ?? '');

        $hasDate = str_contains($normalized, 'y') || str_contains($normalized, 'd');
        $hasTime = str_contains($normalized, 'h') || str_contains($normalized, 's');

        if ($hasDate && $hasTime) {
            return 'datetime';
        }

        if ($hasDate) {
            return 'date';
        }

        if ($hasTime) {
            return 'time';
        }

        return null;
    }

    /**
     * @return array<int, array<string, array{value:mixed,type:?string,style:int|null}>>
     */
    private function parseSheetRows(string $sheetXml, array $sharedStrings, array $styles): array
    {
        $document = new SimpleXMLElement($sheetXml);
        $document->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = [];

        foreach ($document->xpath('//main:sheetData/main:row') as $rowNode) {
            $rowIndex = (int) $rowNode['r'];
            $rows[$rowIndex] = [];

            foreach ($rowNode->c as $cell) {
                $reference = (string) $cell['r'];
                $cellType = isset($cell['t']) ? (string) $cell['t'] : null;
                $styleIndex = isset($cell['s']) ? (int) $cell['s'] : null;
                $column = preg_replace('/\d+/', '', $reference) ?? $reference;
                $value = null;

                if ($cellType === 'inlineStr') {
                    $texts = $cell->xpath('.//*[local-name()="t"]');
                    $value = implode('', array_map(static fn ($node) => (string) $node, $texts));
                } else {
                    $value = isset($cell->v) ? (string) $cell->v : null;
                }

                $rows[$rowIndex][$column] = [
                    'value' => $this->resolveCellValue($value, $cellType, $styleIndex, $sharedStrings, $styles),
                    'type' => $cellType,
                    'style' => $styleIndex,
                ];
            }
        }

        ksort($rows);

        return $rows;
    }

    private function resolveCellValue(mixed $value, ?string $cellType, ?int $styleIndex, array $sharedStrings, array $styles): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($cellType === 's') {
            return $sharedStrings[(int) $value] ?? null;
        }

        if ($cellType === 'b') {
            return (int) $value === 1;
        }

        if ($cellType === 'str' || $cellType === 'inlineStr') {
            return trim((string) $value);
        }

        if (is_numeric($value)) {
            $numeric = (float) $value;
            $styleType = $styleIndex !== null ? ($styles[$styleIndex] ?? null) : null;

            return match ($styleType) {
                'date' => Carbon::create(1899, 12, 30)->addDays((int) floor($numeric))->toDateString(),
                'datetime' => Carbon::create(1899, 12, 30)->addSeconds((int) round($numeric * 86400))->format('Y-m-d H:i:s'),
                'time' => gmdate('H:i:s', (int) round(($numeric - floor($numeric)) * 86400)),
                default => $numeric,
            };
        }

        return trim((string) $value);
    }

    /**
     * @param array<string, array{value:mixed,type:?string,style:int|null}> $row
     * @return array<int, string>
     */
    private function extractHeaders(array $row, array $sharedStrings, array $styles): array
    {
        $headers = [];

        foreach ($row as $column => $cell) {
            $value = $cell['value'];
            $headers[$this->columnToIndex($column)] = is_string($value) ? trim($value) : (string) $value;
        }

        return $headers;
    }

    /**
     * @param array<int, string> $headers
     * @param array<string, array{value:mixed,type:?string,style:int|null}> $row
     * @return array<string, mixed>
     */
    private function mapRow(array $headers, array $row, array $sharedStrings, array $styles): array
    {
        $mapped = [];

        foreach ($headers as $columnIndex => $header) {
            $column = $this->indexToColumn($columnIndex);
            $mapped[$header] = $row[$column]['value'] ?? null;
        }

        return $mapped;
    }

    /**
     * @param array<string, mixed> $mapped
     * @return array<string, mixed>
     */
    private function transformRow(array $mapped, string $sourceWorkbook, string $sourceSheet, int $rowNumber): array
    {
        return [
            'study_code' => 'IAVIC114',
            'source_workbook' => $sourceWorkbook,
            'source_sheet' => $sourceSheet,
            'source_row_number' => $rowNumber,
            'sample_id_visit_number' => $this->cleanString($mapped['Sample ID_Visit Number'] ?? null),
            'report_date' => $this->parseDateValue($mapped['Date'] ?? null),
            'total_blood_volume_ml' => $this->parseDecimal($mapped['Total Blood Volume (ml)'] ?? null, 2),
            'blood_draw_time' => $this->parseTimeValue($mapped['Time of Blood Draw (HH:MM)'] ?? null),
            'sample_condition' => $this->cleanString($mapped['Condition of the Sample (Pass/Fail)'] ?? null),
            'viability_percent' => $this->parseDecimal($mapped['Viability (%)'] ?? null, 2),
            'viable_cells_per_ml_millions' => $this->parseDecimal($mapped["Viable Cells (x10^6)/ml\n"] ?? null, 3),
            'resuspension_volume_ml' => $this->parseDecimal($mapped['Resuspension volume'] ?? null, 2),
            'total_viable_cells_millions' => $this->parseDecimal($mapped['Total Viable Cells (1x10^6)'] ?? null, 2),
            'cell_yield_per_ml_blood' => $this->parseDecimal($mapped['Cell Yield/ml of blood'] ?? null, 3),
            'actual_cells_per_vial_millions' => $this->parseDecimal($mapped['Actual number of cells per vial (N2) = (T/ Vf) x (1mL)'] ?? null, 2),
            'cryovials_frozen' => $this->parseInteger($mapped['Number of cryovials frozen'] ?? null),
            'lab_processing_start_time' => $this->parseTimeValue($mapped['Time at Lab Processing start(HH:MM)'] ?? null),
            'freezing_time' => $this->parseTimeValue($mapped['Time at Freezing (HH:MM)'] ?? null),
            'processing_to_freezing_minutes' => $this->parseDurationMinutes($mapped['Time Taken(from Lab processing start to Freezing)(HH:MM)'] ?? null),
            'blood_draw_to_freezing_minutes' => $this->parseDurationMinutes($mapped['Time taken (Blood Draw-Freezing (HH:MM)'] ?? null),
            'operator_initials' => $this->cleanString($mapped['Operator Initials'] ?? null),
            'comments' => $this->cleanString($mapped['Comments'] ?? null),
            'raw_payload' => $this->normalizeRawPayload($mapped),
        ];
    }

    /**
     * @param array<string, mixed> $mapped
     */
    private function isImportableRow(array $mapped): bool
    {
        $sampleId = $this->cleanString($mapped['Sample ID_Visit Number'] ?? null);
        return !blank($sampleId);
    }

    private function parseDateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return Carbon::parse($value)->toDateString();
        }

        return null;
    }

    private function parseTimeValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return Carbon::parse($value)->format('H:i:s');
        }

        if (is_numeric($value)) {
            return gmdate('H:i:s', (int) round(((float) $value) * 86400));
        }

        return null;
    }

    private function parseDurationMinutes(mixed $value): ?int
    {
        $time = $this->parseTimeValue($value);

        if ($time === null) {
            return null;
        }

        [$hours, $minutes, $seconds] = array_map('intval', explode(':', $time));
        return ($hours * 60) + $minutes + (int) round($seconds / 60);
    }

    private function parseDecimal(mixed $value, int $scale): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $normalized = trim(str_replace(',', '', $value));
            if ($normalized === '') {
                return null;
            }

            if (!is_numeric($normalized)) {
                return null;
            }

            $value = (float) $normalized;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, $scale, '.', '');
    }

    private function parseInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (int) round((float) $value);
    }

    private function cleanString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);
        return $normalized === '' ? null : $normalized;
    }

    /**
     * @param array<string, mixed> $mapped
     * @return array<string, mixed>
     */
    private function normalizeRawPayload(array $mapped): array
    {
        $normalized = [];

        foreach ($mapped as $key => $value) {
            if (is_string($value)) {
                $normalized[$key] = trim($value);
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function columnToIndex(string $column): int
    {
        $index = 0;

        foreach (str_split($column) as $character) {
            $index = ($index * 26) + (ord($character) - 64);
        }

        return $index;
    }

    private function indexToColumn(int $index): string
    {
        $column = '';

        while ($index > 0) {
            $modulo = ($index - 1) % 26;
            $column = chr(65 + $modulo) . $column;
            $index = (int) (($index - $modulo) / 26);
        }

        return $column;
    }
}
