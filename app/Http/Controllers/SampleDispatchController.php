<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSampleDispatchRequest;
use App\Jobs\SendDispatchNotificationEmail;
use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\SampleDispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $dispatches = SampleDispatch::with(['dispatchedBy', 'driverUser', 'receivedBy'])
            ->when($q !== '', fn ($query) => $query->where(function ($subQuery) use ($q) {
                $like = '%' . $q . '%';

                $subQuery->where('sample_id', 'like', $like)
                    ->orWhere('reference', 'like', $like)
                    ->orWhere('study', 'like', $like)
                    ->orWhere('origin_location', 'like', $like)
                    ->orWhere('destination', 'like', $like)
                    ->orWhere('driver_name', 'like', $like)
                    ->orWhere('driver_phone', 'like', $like)
                    ->orWhereHas('dispatchedBy', fn ($userQuery) => $userQuery->where('name', 'like', $like))
                    ->orWhereHas('receivedBy', fn ($userQuery) => $userQuery->where('name', 'like', $like));
            }))
            ->orderByRaw("CASE WHEN status = 'received' THEN 1 ELSE 0 END")
            ->orderBy($sort, $dir)
            ->when($sort !== 'dispatch_date', fn ($query) => $query->orderByDesc('dispatch_date'))
            ->when($sort !== 'created_at', fn ($query) => $query->orderByDesc('created_at'))
            ->paginate(15)
            ->withQueryString();

        $drivers      = Driver::active()->orderBy('name')->get();
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

        if (!$user->isAdmin() && $user->department !== 'Clinical Operations') {
            abort(403, 'Only Clinical Operations staff may dispatch samples.');
        }

        $drivers = Driver::active()->orderBy('name')->get();

        $origins      = config('dispatch.origins');
        $destinations = config('dispatch.destinations');

        return view('sample-dispatches.create', compact('drivers', 'origins', 'destinations'));
    }

    public function store(StoreSampleDispatchRequest $request)
    {
        $data = $request->validated();

        if (!empty($data['driver_user_id'])) {
            $driver = Driver::find($data['driver_user_id']);
            if ($driver) {
                $data['driver_name']  = $driver->name;
                $data['driver_phone'] = $driver->phone_number;
            }
        }

        $dispatch = SampleDispatch::create([
            ...$data,
            'quantity'               => $data['quantity'] ?? 1,
            'dispatched_by_user_id'  => Auth::id(),
        ]);

        SendDispatchNotificationEmail::dispatch($dispatch);

        return redirect()
            ->route('sample-dispatches.show', $dispatch)
            ->with('success', "Sample {$dispatch->sample_id} dispatched. Reference: {$dispatch->reference}");
    }

    public function show(SampleDispatch $sampleDispatch)
    {
        $sampleDispatch->load(['dispatchedBy', 'driverUser', 'receivedBy']);

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
            abort(403, 'Only Laboratory staff may receive samples.');
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

        return back()->with('success', "Sample {$sampleDispatch->sample_id} received by " . Auth::user()->name . '.');
    }

    private function canReceiveSamples($user): bool
    {
        return $user && str_contains(strtolower((string) $user->department), 'lab');
    }
}
