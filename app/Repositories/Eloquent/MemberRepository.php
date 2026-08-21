<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\MemberRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MemberRepository implements MemberRepositoryInterface
{
    public function paginate(int $societyId, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with(['house', 'familyMembers', 'vehicles'])
            ->where('society_id', $societyId)
            ->role('member')
            ->when($search, fn ($q) => $this->applySearch($q, $search))
            ->latest()
            ->paginate($perPage);
    }

    public function find(int $id): ?User
    {
        return User::with(['house', 'familyMembers', 'vehicles', 'society'])->find($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh(['house', 'familyMembers', 'vehicles']);
    }

    public function search(int $societyId, string $query): Collection
    {
        return User::query()
            ->where('society_id', $societyId)
            ->where(fn ($q) => $this->applySearch($q, $query))
            ->limit(20)
            ->get();
    }

    protected function applySearch($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhereHas('house', fn ($h) => $h->where('house_number', 'like', "%{$search}%"))
                ->orWhereHas('vehicles', fn ($v) => $v
                    ->where('car_number', 'like', "%{$search}%")
                    ->orWhere('bike_number', 'like', "%{$search}%"));
        });
    }
}
