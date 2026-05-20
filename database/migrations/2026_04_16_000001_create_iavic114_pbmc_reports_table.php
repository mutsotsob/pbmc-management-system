<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('iavic114_pbmc_reports', function (Blueprint $table) {
            $table->id();

            // Workbook provenance supports traceability during imports and audits.
            $table->string('study_code', 50)->default('IAVIC114');
            $table->string('source_workbook')->nullable();
            $table->string('source_sheet', 100)->nullable();
            $table->unsignedInteger('source_row_number')->nullable();

            // Core identifiers from "Sample ID_Visit Number".
            $table->string('sample_id_visit_number', 32);
            $table->string('participant_id', 24)->nullable();
            $table->string('visit_code', 16)->nullable();

            $table->date('report_date')->nullable();
            $table->decimal('total_blood_volume_ml', 8, 2)->nullable();
            $table->time('blood_draw_time')->nullable();
            $table->string('sample_condition', 20)->nullable();

            $table->decimal('viability_percent', 5, 2)->nullable();
            $table->decimal('viable_cells_per_ml_millions', 10, 3)->nullable();
            $table->decimal('resuspension_volume_ml', 8, 2)->nullable();
            $table->decimal('total_viable_cells_millions', 10, 2)->nullable();
            $table->decimal('cell_yield_per_ml_blood', 8, 3)->nullable();
            $table->decimal('actual_cells_per_vial_millions', 8, 2)->nullable();
            $table->unsignedSmallInteger('cryovials_frozen')->nullable();

            $table->time('lab_processing_start_time')->nullable();
            $table->time('freezing_time')->nullable();

            // Durations are stored as minutes rather than TIME to make analytics safer.
            $table->unsignedSmallInteger('processing_to_freezing_minutes')->nullable();
            $table->unsignedSmallInteger('blood_draw_to_freezing_minutes')->nullable();

            $table->string('operator_initials', 16)->nullable();
            $table->text('comments')->nullable();

            // Retain the original row shape for future reconciliation/import debugging.
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->unique(['source_workbook', 'source_sheet', 'source_row_number'], 'iavic114_source_row_unique');

            $table->index('study_code');
            $table->index('sample_id_visit_number');
            $table->index('participant_id');
            $table->index('visit_code');
            $table->index('report_date');
            $table->index('sample_condition');
            $table->index('operator_initials');
            $table->index(['participant_id', 'visit_code'], 'iavic114_participant_visit_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iavic114_pbmc_reports');
    }
};
