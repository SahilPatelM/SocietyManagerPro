<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MemberRepositoryInterface
{
    public function paginate(int $societyId, ?string $search = null, int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function search(int $societyId, string $query): Collection;
}
