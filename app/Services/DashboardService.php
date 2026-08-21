<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\FinancialTransaction;
use App\Models\House;
use App\Models\MaintenanceBill;
use App\Models\User;
use App\Repositories\Contracts\HouseRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        protected TransactionRepositoryInterface $transactions,
        protected HouseRepositoryInterface $houses,
    ) {}

    public function stats(int $societyId): array
    {
        return Cache::remember("dashboard.{$societyId}", 300, function () use ($societyId) {
            $income = $this->transactions->sumByType($societyId, 'income');
            $expense = $this->transactions->sumByType($societyId, 'expense');
            $houseCounts = $this->houses->counts($societyId);

            $todayCollection = (float) FinancialTransaction::where('society_id', $societyId)
                ->where('type', 'income')
                ->where('category', 'maintenance')
                ->whereDate('transaction_date', today())
                ->sum('amount');

            $currentMonth = (float) FinancialTransaction::where('society_id', $societyId)
                ->where('type', 'income')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount');

            $lastMonth = (float) FinancialTransaction::where('society_id', $societyId)
                ->where('type', 'income')
                ->whereMonth('transaction_date', now()->subMonth()->month)
                ->whereYear('transaction_date', now()->subMonth()->year)
                ->sum('amount');

            $pendingMaintenance = (float) MaintenanceBill::where('society_id', $societyId)
                ->whereIn('status', ['pending', 'overdue', 'partial'])
                ->sum(DB::raw('maintenance_amount + late_fee - paid_amount'));

            return [
                'current_balance' => $income - $expense,
                'total_income' => $income,
                'total_expenses' => $expense,
                'available_balance' => $income - $expense,
                'monthly_collection' => $currentMonth,
                'pending_maintenance' => $pendingMaintenance,
                'total_houses' => $houseCounts['total'],
                'occupied_houses' => $houseCounts['occupied'],
                'vacant_houses' => $houseCounts['vacant'],
                'total_members' => User::where('society_id', $societyId)->role('member')->count(),
                'today_collection' => $todayCollection,
                'current_month_collection' => $currentMonth,
                'last_month_collection' => $lastMonth,
                'complaint_count' => Complaint::where('society_id', $societyId)
                    ->where('status', '!=', 'resolved')->count(),
                'pending_dues' => (float) House::where('society_id', $societyId)->sum('outstanding_amount'),
            ];
        });
    }

    public function charts(int $societyId): array
    {
        return [
            'monthly' => $this->transactions->monthlyTotals($societyId),
            'expense_breakdown' => FinancialTransaction::where('society_id', $societyId)
                ->where('type', 'expense')
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->get(),
        ];
    }
}
