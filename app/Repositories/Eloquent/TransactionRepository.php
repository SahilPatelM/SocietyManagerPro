<?php

namespace App\Repositories\Eloquent;

use App\Models\FinancialTransaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function paginate(int $societyId, ?string $type = null, int $perPage = 20): LengthAwarePaginator
    {
        return FinancialTransaction::query()
            ->with(['house', 'creator', 'attachments'])
            ->where('society_id', $societyId)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('transaction_date')
            ->paginate($perPage);
    }

    public function create(array $data): FinancialTransaction
    {
        return FinancialTransaction::create($data);
    }

    public function update(FinancialTransaction $transaction, array $data): FinancialTransaction
    {
        $transaction->update($data);

        return $transaction->fresh(['attachments']);
    }

    public function delete(FinancialTransaction $transaction): bool
    {
        return (bool) $transaction->delete();
    }

    public function sumByType(int $societyId, string $type, ?string $from = null, ?string $to = null): float
    {
        return (float) FinancialTransaction::query()
            ->where('society_id', $societyId)
            ->where('type', $type)
            ->when($from, fn ($q) => $q->whereDate('transaction_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('transaction_date', '<=', $to))
            ->sum('amount');
    }

    public function monthlyTotals(int $societyId, int $months = 12): Collection
    {
        return DB::table('financial_transactions')
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as month")
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->where('society_id', $societyId)
            ->where('transaction_date', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
