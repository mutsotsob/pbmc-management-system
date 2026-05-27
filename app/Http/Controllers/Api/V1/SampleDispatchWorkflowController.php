<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SampleDispatch;
use App\Services\SampleDispatchWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SampleDispatchWorkflowController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $allowedStatuses = ['dispatched', 'received', 'processed'];
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

        $query = SampleDispatch::query()
            ->with(['items', 'dispatchedBy', 'driverUser', 'receivedBy'])
            ->when(in_array($status, $allowedStatuses, true), fn ($builder) => $builder->where('status', $status))
            ->when($q !== '', fn ($builder) => $builder->where(function ($subQuery) use ($q) {
                $like = '%' . $q . '%';

                $subQuery->where('reference', 'like', $like)
                    ->orWhere('sample_id', 'like', $like)
                    ->orWhere('study', 'like', $like)
                    ->orWhere('visit', 'like', $like)
                    ->orWhere('origin_location', 'like', $like)
                    ->orWhere('destination', 'like', $like)
                    ->orWhere('driver_name', 'like', $like)
                    ->orWhereHas('items', fn ($itemQuery) => $itemQuery->where('participant_id', 'like', $like));
            }))
            ->orderByDesc('dispatch_date')
            ->orderByDesc('created_at');

        return response()->json($query->paginate($perPage));
    }

    public function show(SampleDispatch $sampleDispatch): JsonResponse
    {
        $sampleDispatch->load(['items', 'dispatchedBy', 'driverUser', 'receivedBy']);

        return response()->json([
            'data' => $sampleDispatch,
        ]);
    }

    public function receive(
        Request $request,
        SampleDispatch $sampleDispatch,
        SampleDispatchWorkflowService $workflow
    ): JsonResponse {
        $this->authorize('receive', $sampleDispatch);

        $validated = $request->validate([
            'condition_on_arrival' => ['required', Rule::in(['Good', 'Compromised', 'Rejected'])],
            'rejection_reason' => ['required_if:condition_on_arrival,Rejected', 'nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $dispatch = $workflow->receive($sampleDispatch, $request->user(), $validated);

        return response()->json([
            'message' => 'Sample received successfully.',
            'data' => $dispatch->fresh(['items', 'dispatchedBy', 'driverUser', 'receivedBy']),
        ]);
    }

    public function reject(
        Request $request,
        SampleDispatch $sampleDispatch,
        SampleDispatchWorkflowService $workflow
    ): JsonResponse {
        $this->authorize('reject', $sampleDispatch);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $dispatch = $workflow->reject($sampleDispatch, $request->user(), $validated['rejection_reason']);

        return response()->json([
            'message' => 'Sample rejected successfully.',
            'data' => $dispatch->fresh(['items', 'dispatchedBy', 'driverUser', 'receivedBy']),
        ]);
    }

    public function process(
        Request $request,
        SampleDispatch $sampleDispatch,
        SampleDispatchWorkflowService $workflow
    ): JsonResponse {
        $this->authorize('process', $sampleDispatch);

        $dispatch = $workflow->process($sampleDispatch, $request->user());

        return response()->json([
            'message' => 'Sample processed successfully.',
            'data' => $dispatch->fresh(['items', 'dispatchedBy', 'driverUser', 'receivedBy']),
        ]);
    }
}
