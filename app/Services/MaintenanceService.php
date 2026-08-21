<?php

namespace App\Services;

use App\Enums\MaintenanceBillType;
use App\Models\FinancialTransaction;
use App\Models\House;
use App\Models\MaintenanceBill;
use App\Models\MaintenancePayment;
use App\Models\ParkingAllocation;
use App\Models\User;
use App\Repositories\Contracts\MaintenanceRepositoryInterface;
use App\Services\Audit\AuditLogService;
use App\Services\Notification\FirebaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MaintenanceService
{
    public function __construct(
        protected MaintenanceRepositoryInterface $repository,
        protected FirebaseService $firebase,
        protected AuditLogService $audit,
    ) {}

    public function setupAndGenerate(
        int $societyId,
        string $monthYear,
        float $amount,
        string $dueDate,
        float $lateFee,
        int $createdBy,
        bool $sendNotifications = true,
        string $cycleType = 'general',
    ): array {
        return DB::transaction(function () use ($societyId, $monthYear, $amount, $dueDate, $lateFee, $createdBy, $sendNotifications, $cycleType) {
            $cycle = $this->repository->upsertCycle([
                'society_id' => $societyId,
                'month_year' => $monthYear,
                'cycle_type' => $cycleType,
                'amount' => $amount,
                'late_fee' => $lateFee,
                'due_date' => $dueDate,
                'created_by' => $createdBy,
            ]);

            $created = 0;
            $skipped = 0;

            $houses = $this->housesForCycle($societyId, $cycleType);

            foreach ($houses as $house) {
                if ($this->repository->billExists($house->id, $monthYear, $cycleType)) {
                    $skipped++;

                    continue;
                }

                $bill = MaintenanceBill::create([
                    'society_id' => $societyId,
                    'house_id' => $house->id,
                    'bill_number' => $this->billNumber($monthYear, $house->house_number, $cycleType),
                    'month_year' => $monthYear,
                    'bill_type' => $cycleType,
                    'maintenance_amount' => $amount,
                    'late_fee' => $lateFee,
                    'due_date' => $dueDate,
                    'status' => now()->parse($dueDate)->isPast() ? 'overdue' : 'pending',
                    'paid_amount' => 0,
                ]);

                $house->increment('outstanding_amount', $bill->totalDue());
                $created++;
            }

            $cycle->update(['bills_generated' => true]);

            if ($sendNotifications) {
                $this->notifyForCycle($societyId, $monthYear, $amount, $dueDate, $cycleType, $houses);
                $cycle->update(['notifications_sent_at' => now()]);
            }

            $this->audit->log('maintenance.generated', $cycle);

            return compact('created', 'skipped', 'cycle');
        });
    }

    protected function housesForCycle(int $societyId, string $cycleType)
    {
        if ($cycleType === MaintenanceBillType::Parking->value) {
            $houseIds = ParkingAllocation::query()
                ->active()
                ->whereHas('slot', fn ($q) => $q->where('society_id', $societyId)->where('status', 'occupied'))
                ->pluck('house_id')
                ->unique();

            return House::where('society_id', $societyId)
                ->whereIn('id', $houseIds)
                ->get();
        }

        return House::where('society_id', $societyId)->get();
    }

    protected function notifyForCycle(
        int $societyId,
        string $monthYear,
        float $amount,
        string $dueDate,
        string $cycleType,
        $houses,
    ): void {
        if ($cycleType === MaintenanceBillType::Parking->value) {
            $this->notifyParkingOwners($houses, $monthYear, $amount, $dueDate);

            return;
        }

        $this->notifyAllMembers($societyId, $monthYear, $amount, $dueDate);
    }

    protected function notifyParkingOwners($houses, string $monthYear, float $amount, string $dueDate): void
    {
        $monthLabel = $this->formatMonth($monthYear);

        foreach ($houses as $house) {
            if (! $house->owner_user_id) {
                continue;
            }

            $owner = User::find($house->owner_user_id);
            if ($owner && $owner->status === 'active') {
                $this->firebase->sendToUser(
                    $owner,
                    __('app.maintenance_parking_due_title'),
                    __('app.maintenance_parking_due_body', [
                        'month' => $monthLabel,
                        'amount' => number_format($amount),
                        'due' => now()->parse($dueDate)->format('d M Y'),
                        'house' => $house->house_number,
                    ]),
                    'maintenance_parking_due',
                    ['month_year' => $monthYear, 'bill_type' => 'parking']
                );
            }
        }
    }

    public function notifyAllMembers(int $societyId, string $monthYear, float $amount, string $dueDate): void
    {
        $monthLabel = $this->formatMonth($monthYear);

        User::where('society_id', $societyId)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->where('name', 'member'))
            ->each(function (User $user) use ($monthLabel, $amount, $dueDate, $monthYear) {
                $this->firebase->sendToUser(
                    $user,
                    __('app.maintenance_due_title'),
                    __('app.maintenance_due_body', [
                        'month' => $monthLabel,
                        'amount' => number_format($amount),
                        'due' => now()->parse($dueDate)->format('d M Y'),
                    ]),
                    'maintenance_due',
                    ['month_year' => $monthYear]
                );
            });
    }

    public function markAsPaid(
        MaintenanceBill $bill,
        float $amount,
        string $paymentMethod,
        int $receivedBy,
        ?string $paymentDate = null,
    ): MaintenancePayment {
        return DB::transaction(function () use ($bill, $amount, $paymentMethod, $receivedBy, $paymentDate) {
            $paymentDate = $paymentDate ?? now()->toDateString();
            $balance = $bill->balanceDue();

            if ($amount <= 0 || $amount > $balance) {
                throw new \InvalidArgumentException('Invalid payment amount.');
            }

            $payment = MaintenancePayment::create([
                'maintenance_bill_id' => $bill->id,
                'house_id' => $bill->house_id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'receipt_number' => 'RCP-'.strtoupper(Str::random(8)),
                'payment_date' => $paymentDate,
                'received_by' => $receivedBy,
            ]);

            $bill->increment('paid_amount', $amount);
            $bill->refresh();
            $bill->refreshStatus();

            $house = $bill->house;
            $house->decrement('outstanding_amount', $amount);
            $house->increment('paid_amount', $amount);
            $house->update(['last_payment_date' => $paymentDate]);

            FinancialTransaction::create([
                'society_id' => $bill->society_id,
                'type' => 'income',
                'category' => 'maintenance',
                'subcategory' => $bill->month_year,
                'amount' => $amount,
                'transaction_date' => $paymentDate,
                'payment_method' => $paymentMethod,
                'reference_number' => $payment->receipt_number,
                'description' => ucfirst($bill->bill_type)." maintenance {$bill->month_year} - House {$house->house_number}",
                'house_id' => $house->id,
                'created_by' => $receivedBy,
            ]);

            if ($bill->house->owner_user_id) {
                $owner = User::find($bill->house->owner_user_id);
                if ($owner) {
                    $this->firebase->sendToUser(
                        $owner,
                        __('app.maintenance_paid_title'),
                        __('app.maintenance_paid_body', [
                            'month' => $this->formatMonth($bill->month_year),
                            'amount' => number_format($amount),
                        ]),
                        'maintenance_paid',
                        ['bill_id' => $bill->id]
                    );
                }
            }

            $this->audit->log('maintenance.paid', $payment);

            return $payment->load('bill');
        });
    }

    public function accountReport(int $houseId): array
    {
        $house = House::with('owner')->findOrFail($houseId);
        $hasActiveParking = $house->hasActiveParking();

        $bills = $this->repository->billsForHouse($houseId)
            ->filter(fn ($bill) => ($bill->bill_type ?? 'general') !== MaintenanceBillType::Parking->value
                || $hasActiveParking);

        $totalBilled = $bills->sum(fn ($b) => $b->totalDue());
        $totalPaid = $bills->sum('paid_amount');
        $totalPending = $bills->sum(fn ($b) => $b->balanceDue());

        return [
            'house' => $house,
            'bills' => $bills->values(),
            'has_active_parking' => $hasActiveParking,
            'summary' => [
                'total_billed' => $totalBilled,
                'total_paid' => $totalPaid,
                'total_pending' => $totalPending,
                'months_paid' => $bills->where('status', 'paid')->count(),
                'months_pending' => $bills->whereIn('status', ['pending', 'partial', 'overdue'])->count(),
            ],
        ];
    }

    protected function billNumber(string $monthYear, string $houseNumber, string $billType = 'general'): string
    {
        $prefix = $billType === MaintenanceBillType::Parking->value ? 'MBP' : 'MB';

        return $prefix.'-'.str_replace('-', '', $monthYear).'-'.preg_replace('/[^A-Za-z0-9]/', '', $houseNumber);
    }

    protected function formatMonth(string $monthYear): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $monthYear)->format('F Y');
    }
}
