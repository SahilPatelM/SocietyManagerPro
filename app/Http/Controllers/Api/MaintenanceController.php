<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaintenanceBillResource;
use App\Models\MaintenanceBill;
use App\Repositories\Contracts\MaintenanceRepositoryInterface;
use App\Services\MaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function __construct(
        protected MaintenanceRepositoryInterface $repository,
        protected MaintenanceService $service,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasAnyRole(['society_admin', 'treasurer', 'super_admin'])) {
            return MaintenanceBillResource::collection(
                $this->repository->paginateForSociety(
                    $user->society_id,
                    $request->get('month_year'),
                    $request->get('status')
                )
            );
        }

        if (! $user->house_id) {
            return response()->json(['data' => []]);
        }

        return MaintenanceBillResource::collection(
            $this->repository->billsForHouse($user->house_id)
        );
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'month_year' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date',
            'late_fee' => 'nullable|numeric|min:0',
            'send_notifications' => 'boolean',
            'cycle_type' => 'nullable|in:general,parking',
        ]);

        abort_unless($request->user()->hasAnyRole(['society_admin', 'treasurer', 'super_admin']), 403);

        $result = $this->service->setupAndGenerate(
            $request->user()->society_id,
            $request->month_year,
            (float) $request->amount,
            $request->due_date,
            (float) ($request->late_fee ?? 0),
            $request->user()->id,
            $request->boolean('send_notifications', true),
            $request->get('cycle_type', 'general'),
        );

        return response()->json([
            'message' => __('app.maintenance_generated', ['count' => $result['created']]),
            'created' => $result['created'],
            'skipped' => $result['skipped'],
        ]);
    }

    public function markPaid(Request $request, MaintenanceBill $bill): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['society_admin', 'treasurer', 'super_admin']), 403);
        abort_unless($bill->society_id === $request->user()->society_id, 403);

        $data = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'payment_method' => 'required|in:cash,online,upi,bank_transfer',
            'payment_date' => 'nullable|date',
        ]);

        $amount = $data['amount'] ?? $bill->balanceDue();

        $payment = $this->service->markAsPaid(
            $bill,
            (float) $amount,
            $data['payment_method'],
            $request->user()->id,
            $data['payment_date'] ?? null,
        );

        return response()->json([
            'message' => __('app.maintenance_marked_paid'),
            'payment' => $payment,
            'bill' => new MaintenanceBillResource($bill->fresh(['house', 'payments'])),
        ]);
    }

    public function accountReport(Request $request): JsonResponse
    {
        $houseId = $request->user()->hasAnyRole(['society_admin', 'treasurer', 'super_admin'])
            ? $request->validate(['house_id' => 'required|exists:houses,id'])['house_id']
            : $request->user()->house_id;

        abort_if(! $houseId, 404);

        return response()->json($this->service->accountReport((int) $houseId));
    }

    public function currentCycle(Request $request): JsonResponse
    {
        $monthYear = $request->get('month_year', now()->format('Y-m'));
        $cycle = $this->repository->getCycle(
            $request->user()->society_id,
            $monthYear,
            $request->get('cycle_type', 'general'),
        );

        return response()->json(['cycle' => $cycle]);
    }
}
