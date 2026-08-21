<?php

namespace App\Repositories\Eloquent;

use App\Models\MaintenanceBill;
use App\Models\MaintenanceCycle;
use App\Repositories\Contracts\MaintenanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MaintenanceRepository implements MaintenanceRepositoryInterface
{
    public function getCycle(int $societyId, string $monthYear, string $cycleType = 'general'): ?MaintenanceCycle
    {
        return MaintenanceCycle::where('society_id', $societyId)
            ->where('month_year', $monthYear)
            ->where('cycle_type', $cycleType)
            ->first();
    }

    public function upsertCycle(array $data): MaintenanceCycle
    {
        return MaintenanceCycle::updateOrCreate(
            [
                'society_id' => $data['society_id'],
                'month_year' => $data['month_year'],
                'cycle_type' => $data['cycle_type'] ?? 'general',
            ],
            $data
        );
    }

    public function billsForSociety(int $societyId, ?string $monthYear = null, ?string $billType = null): Collection
    {
        return MaintenanceBill::with(['house.owner', 'payments'])
            ->where('society_id', $societyId)
            ->when($monthYear, fn ($q) => $q->where('month_year', $monthYear))
            ->when($billType, fn ($q) => $q->where('bill_type', $billType))
            ->orderByDesc('month_year')
            ->orderBy('house_id')
            ->get();
    }

    public function billsForHouse(int $houseId): Collection
    {
        return MaintenanceBill::with(['payments.receivedBy'])
            ->where('house_id', $houseId)
            ->orderByDesc('month_year')
            ->orderByDesc('bill_type')
            ->get();
    }

    public function paginateForSociety(
        int $societyId,
        ?string $monthYear = null,
        ?string $status = null,
        ?string $search = null,
        ?string $billType = null,
    ): LengthAwarePaginator {
        return MaintenanceBill::with(['house.owner', 'payments'])
            ->where('society_id', $societyId)
            ->when($monthYear, fn ($q) => $q->where('month_year', $monthYear))
            ->when($billType, fn ($q) => $q->where('bill_type', $billType))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('bill_number', 'like', "%{$search}%")
                        ->orWhereHas('house', function ($house) use ($search) {
                            $house->where('house_number', 'like', "%{$search}%")
                                ->orWhereHas('owner', function ($owner) use ($search) {
                                    $owner->where('name', 'like', "%{$search}%")
                                        ->orWhere('mobile', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->orderByDesc('month_year')
            ->orderBy('house_id')
            ->paginate(20);
    }

    public function findBill(int $id): ?MaintenanceBill
    {
        return MaintenanceBill::with(['house.owner', 'payments'])->find($id);
    }

    public function billExists(int $houseId, string $monthYear, string $billType = 'general'): bool
    {
        return MaintenanceBill::where('house_id', $houseId)
            ->where('month_year', $monthYear)
            ->where('bill_type', $billType)
            ->exists();
    }
}
