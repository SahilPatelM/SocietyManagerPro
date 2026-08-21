<?php

namespace App\Repositories\Contracts;

use App\Models\FinancialTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    public function paginate(int $societyId, ?string $type = null, int $perPage = 20): LengthAwarePaginator;

    public function create(array $data): FinancialTransaction;

    public function update(FinancialTransaction $transaction, array $data): FinancialTransaction;

    public function delete(FinancialTransaction $transaction): bool;

    public function sumByType(int $societyId, string $type, ?string $from = null, ?string $to = null): float;

    public function monthlyTotals(int $societyId, int $months = 12): Collection;
}
