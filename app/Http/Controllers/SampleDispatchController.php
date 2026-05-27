<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSampleDispatchRequest;
use App\Jobs\SendDispatchNotificationEmail;
use App\Jobs\SendSampleRejectionNotificationEmail;
use App\Models\AuditLog;
use App\Models\SampleDispatch;
use App\Models\User;
use App\Notifications\SampleDispatchRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class SampleDispatchController extends Controller
{
    public function index(Request $request)
    {
        $origins = config('dispatch.origins');
        $destinations = config('dispatch.destinations');

        $q = trim((string) $request->query('q', ''));

        $allowedSorts = ['reference', 'dispatch_date', 'sample_id', 'origin_location', 'destination', 'driver_name', 'status', 'created_at'];
        $sort = in_array($request->query('sort'), $allowedSorts, true)
            ? $request->query('sort')
            : 'dispatch_date';
        $dir = $request->query('dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $dispatches = SampleDispatch::with(['dispatchedBy', 'driverUser', 'receivedBy', 'items'])
            ->when($q !== '', fn ($query) => $query->where(function ($subQuery) use ($q) {
                $like = '%' . $q . '%';

                $subQuery->where('sample_id', 'like', $like)
                    ->orWhere('reference', 'like', $like)
                    ->orWhere('study', 'like', $like)
                    ->orWhere('origin_location', 'like', $like)
                    ->orWhere('destination', 'like', $like)
                    ->orWhere('driver_name', 'like', $like)
                    ->orWhere('driver_phone', 'like', $like)
                    ->orWhereHas('items', fn ($itemQuery) => $itemQuery->where('participant_id', 'like', $like))
                    ->orWhereHas('dispatchedBy', fn ($userQuery) => $userQuery->where('name', 'like', $like))
                    ->orWhereHas('receivedBy', fn ($userQuery) => $userQuery->where('name', 'like', $like));
            }))
            ->orderByRaw("CASE WHEN status = 'received' THEN 1 ELSE 0 END")
            ->orderBy($sort, $dir)
            ->when($sort !== 'dispatch_date', fn ($query) => $query->orderByDesc('dispatch_date'))
            ->when($sort !== 'created_at', fn ($query) => $query->orderByDesc('created_at'))
            ->paginate(15)
            ->withQueryString();

        $drivers = User::where('department', 'Administration')
            ->where('user_status', true)
            ->orderBy('name')
            ->get();
        $canReceiveSamples = $this->canReceiveSamples($request->user());

        return view('sample-dispatches.index', compact(
            'dispatches',
            'sort',
            'dir',
            'drivers',
            'origins',
            'destinations',
            'q',
            'canReceiveSamples',
        ));
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user->hasFullSystemAccess() && !$user->isDepartment('Clinical Operations')) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Only Clinical Operations staff may dispatch samples.');
        }

        $drivers = User::where('department', 'Administration')
            ->where('user_status', true)
            ->orderBy('name')
            ->get();

        $origins      = config('dispatch.origins');
        $destinations = config('dispatch.destinations');

        return view('sample-dispatches.create', compact('drivers', 'origins', 'destinations'));
    }

    public function store(StoreSampleDispatchRequest $request)
    {
        $data = $request->validated();

        $dispatch = $this->createDispatch($data);

        SendDispatchNotificationEmail::dispatch($dispatch);

        return redirect()
            ->route('sample-dispatches.show', $dispatch)
            ->with('success', "Dispatch {$dispatch->reference} created with {$dispatch->quantity} sample(s).");
    }

    public function bulk(Request $request)
    {
        $user = $request->user();

        if (!$user || (!$user->hasFullSystemAccess() && !$user->isDepartment('Clinical Operations'))) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Only Clinical Operations staff may dispatch samples.');
        }

        $validated = $request->validate([
            'bulk_rows' => ['required', 'array', 'min:1'],
            'bulk_rows.*.participant_id' => ['required', 'string', 'max:100'],
            'bulk_rows.*.study' => ['required', Rule::in(config('dispatch.studies'))],
            'bulk_rows.*.visit' => ['nullable', 'string', 'max:20'],
            'bulk_rows.*.no_of_bags' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'bulk_rows.*.dispatch_date' => ['required', 'date'],
            'bulk_rows.*.dispatch_time' => ['nullable', 'date_format:H:i'],
            'bulk_rows.*.origin_location' => ['required', Rule::in(config('dispatch.origins'))],
            'bulk_rows.*.destination' => ['required', Rule::in(config('dispatch.destinations'))],
            'bulk_rows.*.driver_user_id' => ['nullable', 'exists:users,id'],
            'bulk_rows.*.driver_name' => ['nullable', 'string', 'max:150'],
            'bulk_rows.*.driver_phone' => ['nullable', 'string', 'max:30'],
            'bulk_rows.*.description' => ['nullable', 'string'],
        ]);

        $dispatches = DB::transaction(function () use ($validated) {
            return collect($validated['bulk_rows'])
                ->map(fn (array $row) => $this->createDispatch([
                    'participant_ids' => [$row['participant_id']],
                    'study' => $row['study'],
                    'visit' => $row['visit'] ?? null,
                    'no_of_bags' => $row['no_of_bags'] ?? null,
                    'dispatch_date' => $row['dispatch_date'],
                    'dispatch_time' => $row['dispatch_time'] ?? null,
                    'origin_location' => $row['origin_location'],
                    'destination' => $row['destination'],
                    'driver_user_id' => $row['driver_user_id'] ?? null,
                    'driver_name' => $row['driver_name'] ?? '',
                    'driver_phone' => $row['driver_phone'] ?? null,
                    'description' => $row['description'] ?? null,
                ]));
        });

        $dispatches->each(fn (SampleDispatch $dispatch) => SendDispatchNotificationEmail::dispatch($dispatch));

        return redirect()
            ->route('sample-dispatches.index')
            ->with('success', $dispatches->count() . ' bulk dispatch sample(s) created.');
    }

    private function createDispatch(array $data): SampleDispatch
    {
        if (!empty($data['driver_user_id'])) {
            $driver = User::find($data['driver_user_id']);
            if ($driver) {
                $data['driver_name']  = $driver->name;
                $data['driver_phone'] = $driver->phone_number;
            }
        }

        $participantIds = collect($data['participant_ids'] ?? [])
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();

        if (!empty($data['description'])) {
            $data['notes'] = $data['description'];
        }

        $dispatchData = Arr::except($data, ['participant_ids', 'description']);
        $dispatchData['driver_name'] ??= '';

        $dispatch = SampleDispatch::create([
            ...$dispatchData,
            'sample_id'             => $participantIds->first() ?? '',
            'quantity'              => max($participantIds->count(), 1),
            'dispatched_by_user_id' => Auth::id(),
        ]);

        if ($participantIds->isNotEmpty()) {
            $dispatch->items()->createMany(
                $participantIds->map(fn ($participantId) => ['participant_id' => $participantId])->all()
            );
        }

        return $dispatch;
    }

    public function show(SampleDispatch $sampleDispatch)
    {
        $sampleDispatch->load(['dispatchedBy', 'driverUser', 'receivedBy', 'items']);

        $history = AuditLog::where('auditable_type', SampleDispatch::class)
            ->where('auditable_id', $sampleDispatch->id)
            ->orderBy('created_at')
            ->get();

        $conditions = config('dispatch.conditions_on_arrival');

        return view('sample-dispatches.show', [
            'dispatch'   => $sampleDispatch,
            'history'    => $history,
            'conditions' => $conditions,
            'canReceiveSamples' => $this->canReceiveSamples(request()->user()),
        ]);
    }

    public function receive(Request $request, SampleDispatch $sampleDispatch)
    {
        if (!$this->canReceiveSamples($request->user())) {
            return back()->with('error', 'Only Laboratory staff may receive samples.');
        }

        if ($sampleDispatch->isReceived()) {
            return back()->with('error', 'This sample has already been marked as received.');
        }

        $validated = $request->validate([
            'condition_on_arrival' => ['required', 'in:Good,Compromised,Rejected'],
            'rejection_reason'     => ['required_if:condition_on_arrival,Rejected', 'nullable', 'string', 'max:500'],
            'notes'                => ['nullable', 'string', 'max:1000'],
        ]);

        $sampleDispatch->update([
            ...$validated,
            'status'               => 'received',
            'received_at'          => now(),
            'received_by_user_id'  => Auth::id(),
        ]);

        $sampleDispatch->refresh();

        if ($sampleDispatch->condition_on_arrival === 'Rejected') {
            $this->notifyRejectedDispatch($sampleDispatch);

            return back()->with('success', "Dispatch {$sampleDispatch->reference} rejected. Driver and Clinical Operations were notified.");
        }

        return back()->with('success', "Dispatch {$sampleDispatch->reference} received by " . Auth::user()->name . '.');
    }

    public function reject(Request $request, SampleDispatch $sampleDispatch)
    {
        if (!$this->canReceiveSamples($request->user())) {
            return back()->with('error', 'Only Laboratory staff may reject samples.');
        }

        if ($sampleDispatch->isReceived()) {
            return back()->with('error', 'This sample has already been marked as received.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $sampleDispatch->update([
            'status' => 'received',
            'received_at' => now(),
            'received_by_user_id' => Auth::id(),
            'condition_on_arrival' => 'Rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $sampleDispatch->refresh();
        $this->notifyRejectedDispatch($sampleDispatch);

        return redirect()
            ->route('dashboard')
            ->with('success', "Dispatch {$sampleDispatch->reference} rejected. Driver and Clinical Operations were notified.");
    }

    private function canReceiveSamples($user): bool
    {
        return $user && ($user->hasFullAccessDepartment() || str_contains(strtolower((string) $user->department), 'lab'));
    }

    private function notifyRejectedDispatch(SampleDispatch $dispatch): void
    {
        $dispatch->loadMissing(['driverUser', 'dispatchedBy', 'receivedBy']);

        $recipients = $this->rejectionRecipients($dispatch);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new SampleDispatchRejectedNotification($dispatch));

        $emails = $recipients
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($emails)) {
            SendSampleRejectionNotificationEmail::dispatch($dispatch, $emails);
        }
    }

    private function rejectionRecipients(SampleDispatch $dispatch)
    {
        $clinicalOperationsUsers = User::query()
            ->where('department', 'Clinical Operations')
            ->where('user_status', true)
            ->get();

        return $clinicalOperationsUsers
            ->push($dispatch->driverUser)
            ->push($dispatch->dispatchedBy)
            ->filter()
            ->unique('id')
            ->values();
    }
}
