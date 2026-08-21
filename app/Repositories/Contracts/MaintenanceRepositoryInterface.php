<?php

namespace App\Repositories\Contracts;

use App\Models\MaintenanceBill;
use App\Models\MaintenanceCycle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MaintenanceRepositoryInterface
{
    public function getCycle(int $societyId, string $monthYear, string $cycleType = 'general'): ?MaintenanceCycle;

    public function upsertCycle(array $data): MaintenanceCycle;

    public function billsForSociety(int $societyId, ?string $monthYear = null, ?string $billType = null): Collection;

    public function billsForHouse(int $houseId): Collection;

    public function paginateForSociety(
        int $societyId,
        ?string $monthYear = null,
        ?string $status = null,
        ?string $search = null,
        ?string $billType = null,
    ): LengthAwarePaginator;

    public function findBill(int $id): ?MaintenanceBill;

    public function billExists(int $houseId, string $monthYear, string $billType = 'general'): bool;
}
