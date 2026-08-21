<?php

namespace App\Livewire\Maintenance;

use App\Livewire\Concerns\ShowsToast;
use App\Models\MaintenanceBill;
use App\Repositories\Contracts\MaintenanceRepositoryInterface;
use App\Services\MaintenanceService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use ShowsToast;
    use WithPagination;

    public string $monthYear = '';

    public string $filterStatus = '';

    public string $search = '';

    public float $amount = 1500;

    public float $lateFee = 0;

    public string $dueDate = '';

    public bool $sendNotifications = true;

    public ?int $payBillId = null;

    public string $paymentMethod = 'cash';

    public float $payAmount = 0;

    public bool $showGenerate = false;

    public string $maintenanceType = 'general';

    public bool $showPayModal = false;

    public string $payHouseNumber = '';

    public string $payOwnerName = '';

    public string $payBillNumber = '';

    public float $payBalanceDue = 0;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingMaintenanceType(): void
    {
        $this->resetPage();
        $this->loadCycleAmounts();
    }

    public function mount(): void
    {
        $this->monthYear = now()->format('Y-m');
        $this->dueDate = now()->endOfMonth()->format('Y-m-d');
        $this->loadCycleAmounts();
    }

    public function updatedMonthYear(): void
    {
        $this->resetPage();
        $this->loadCycleAmounts();
    }

    protected function loadCycleAmounts(): void
    {
        $cycle = app(MaintenanceRepositoryInterface::class)
            ->getCycle(auth()->user()->society_id, $this->monthYear, $this->maintenanceType);

        if ($cycle) {
            $this->amount = (float) $cycle->amount;
            $this->lateFee = (float) $cycle->late_fee;
            $this->dueDate = $cycle->due_date->format('Y-m-d');
        }
    }

    public function generateBills(MaintenanceService $service): void
    {
        $this->validate([
            'monthYear' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:1',
            'dueDate' => 'required|date',
            'lateFee' => 'numeric|min:0',
        ]);

        abort_unless(auth()->user()->canManageMaintenance(), 403);

        $result = $service->setupAndGenerate(
            auth()->user()->society_id,
            $this->monthYear,
            $this->amount,
            $this->dueDate,
            $this->lateFee,
            auth()->id(),
            $this->sendNotifications,
            $this->maintenanceType,
        );

        if ($this->maintenanceType === 'parking' && $result['created'] === 0) {
            $this->toastError(__('app.maintenance_parking_no_slots'));
        } else {
            $message = $this->maintenanceType === 'parking'
                ? __('app.maintenance_parking_generated', ['count' => $result['created']])
                : __('app.maintenance_generated', ['count' => $result['created']]);
            $this->toastSuccess($message);
        }

        $this->showGenerate = false;
    }

    public function openPayModal(int $billId): void
    {
        $bill = MaintenanceBill::findOrFail($billId);
        abort_unless(auth()->user()->canManageMaintenance(), 403);

        $bill->load(['house.owner']);

        $this->payBillId = $billId;
        $this->payAmount = $bill->balanceDue();
        $this->payBalanceDue = $bill->balanceDue();
        $this->payHouseNumber = $bill->house?->house_number ?? '—';
        $this->payOwnerName = $bill->house?->owner?->name ?? '—';
        $this->payBillNumber = $bill->bill_number;
        $this->paymentMethod = 'cash';
        $this->showPayModal = true;
    }

    public function closePayModal(): void
    {
        $this->showPayModal = false;
        $this->payBillId = null;
    }

    public function payFullAmount(): void
    {
        $this->payAmount = $this->payBalanceDue;
    }

    public function markPaid(MaintenanceService $service): void
    {
        abort_unless(auth()->user()->canManageMaintenance(), 403);

        $bill = MaintenanceBill::findOrFail($this->payBillId);

        $this->validate([
            'payAmount' => 'required|numeric|min:0.01|max:'.$bill->balanceDue(),
            'paymentMethod' => 'required|in:cash,online,upi,bank_transfer',
        ]);

        $service->markAsPaid($bill, $this->payAmount, $this->paymentMethod, auth()->id());

        $this->toastSuccess(__('app.maintenance_marked_paid'));
        $this->closePayModal();
    }

    public function render(MaintenanceRepositoryInterface $repository, MaintenanceService $service)
    {
        $user = auth()->user();
        $isAdmin = $user->canManageMaintenance();

        if ($isAdmin) {
            $bills = $repository->paginateForSociety(
                $user->society_id,
                $this->monthYear ?: null,
                $this->filterStatus ?: null,
                $this->search ?: null,
                $this->maintenanceType,
            );
            $cycle = $repository->getCycle($user->society_id, $this->monthYear, $this->maintenanceType);
            $accountReport = null;
        } else {
            $bills = collect();
            $cycle = null;
            $accountReport = $user->house_id
                ? $service->accountReport($user->house_id)
                : null;
        }

        return view('livewire.maintenance.index', compact('bills', 'cycle', 'isAdmin', 'accountReport'))
            ->layout('layouts.mobile', ['title' => __('app.maintenance')]);
    }
}
