<?php

namespace App\Repositories\Contracts;

use App\Models\House;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface HouseRepositoryInterface
{
    public function paginate(int $societyId, ?string $search = null): LengthAwarePaginator;

    public function find(int $id): ?House;

    public function ledger(int $houseId): Collection;

    public function counts(int $societyId): array;
}
