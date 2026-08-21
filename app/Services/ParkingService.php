<?php

namespace App\Services;

use App\Models\ParkingAllocation;
use App\Models\ParkingSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ParkingService
{
    public function createSlot(int $societyId, string $slotNumber): ParkingSlot
    {
        return ParkingSlot::create([
            'society_id' => $societyId,
            'slot_number' => strtoupper(trim($slotNumber)),
            'status' => 'available',
        ]);
    }

    public function assignToHousehold(
        int $societyId,
        int $houseId,
        string $slotNumber,
        ?string $vehicleNumber = null,
    ): ParkingAllocation {
        if (ParkingAllocation::query()
            ->active()
            ->where('house_id', $houseId)
            ->whereHas('slot', fn ($q) => $q->where('society_id', $societyId))
            ->exists()) {
            throw ValidationException::withMessages([
                'allocateHouseId' => __('app.parking_house_already_assigned'),
            ]);
        }

        $slotNumber = strtoupper(trim($slotNumber));

        return DB::transaction(function () use ($societyId, $houseId, $slotNumber, $vehicleNumber) {
            $slot = ParkingSlot::where('society_id', $societyId)
                ->whereRaw('UPPER(slot_number) = ?', [$slotNumber])
                ->first();

            if ($slot) {
                if ($slot->status === 'occupied') {
                    throw ValidationException::withMessages([
                        'slotNumber' => __('app.parking_slot_occupied'),
                    ]);
                }
            } else {
                $slot = $this->createSlot($societyId, $slotNumber);
            }

            return $this->allocate($slot, $houseId, $vehicleNumber ?? '—');
        });
    }

    public function allocate(ParkingSlot $slot, int $houseId, string $vehicleNumber): ParkingAllocation
    {
        $allocation = ParkingAllocation::create([
            'parking_slot_id' => $slot->id,
            'house_id' => $houseId,
            'vehicle_number' => $vehicleNumber,
            'allocated_from' => now()->toDateString(),
            'allocated_until' => null,
        ]);

        $slot->update(['status' => 'occupied']);

        return $allocation->load(['house', 'slot']);
    }

    public function release(ParkingSlot $slot): void
    {
        DB::transaction(function () use ($slot) {
            ParkingAllocation::where('parking_slot_id', $slot->id)
                ->whereNull('allocated_until')
                ->update(['allocated_until' => now()->toDateString()]);

            $slot->update(['status' => 'available']);
        });
    }
}
