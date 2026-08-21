<?php

namespace App\Livewire\Reports;

use App\Services\MaintenanceService;
use Livewire\Component;

class AccountReport extends Component
{
    public function render(MaintenanceService $service)
    {
        $user = auth()->user();
        $report = $user->house_id
            ? $service->accountReport($user->house_id)
            : null;

        return view('livewire.reports.account-report', compact('report'))
            ->layout('layouts.mobile', ['title' => __('app.account_report')]);
    }
}
