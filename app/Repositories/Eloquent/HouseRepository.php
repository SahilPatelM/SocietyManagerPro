<?php

namespace App\Repositories\Eloquent;

use App\Models\House;
use App\Models\MaintenancePayment;
use App\Repositories\Contracts\HouseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class HouseRepository implements HouseRepositoryInterface
{
    public function paginate(int $societyId, ?string $search = null): LengthAwarePaginator
    {
        return House::query()
            ->with(['owner', 'block'])
            ->where('society_id', $societyId)
            ->when($search, fn ($q) => $q->where('house_number', 'like', "%{$search}%")
                ->orWhereHas('owner', fn ($o) => $o->where('name', 'like', "%{$search}%")))
            ->orderBy('house_number')
            ->paginate(20);
    }

    public function find(int $id): ?House
    {
        return House::with(['owner', 'block', 'maintenanceBills', 'maintenancePayments'])->find($id);
    }

    public function ledger(int $houseId): Collection
    {
        $payments = MaintenancePayment::where('house_id', $houseId)
            ->with('bill')
            ->orderByDesc('payment_date')
            ->get();

        return $payments;
    }

    public function counts(int $societyId): array
    {
        $total = House::where('society_id', $societyId)->count();
        $occupied = House::where('society_id', $societyId)->where('status', 'occupied')->count();

        return [
            'total' => $total,
            'occupied' => $occupied,
            'vacant' => $total - $occupied,
        ];
    }
}
