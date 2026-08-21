<?php

namespace App\Livewire\Parking;

use App\Livewire\Concerns\ShowsToast;
use App\Models\House;
use App\Models\ParkingSlot;
use App\Services\ParkingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Index extends Component
{
    use ShowsToast;

    public bool $showAssign = false;

    public string $allocateHouseId = '';

    public string $slotNumber = '';

    public string $vehicleNumber = '';

    public function assignToHousehold(ParkingService $service): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $societyId = (int) auth()->user()->society_id;

        $this->validate([
            'allocateHouseId' => [
                'required',
                Rule::exists('houses', 'id')->where('society_id', $societyId),
            ],
            'slotNumber' => 'required|string|max:20',
            'vehicleNumber' => 'nullable|string|max:50',
        ]);

        try {
            $service->assignToHousehold(
                $societyId,
                (int) $this->allocateHouseId,
                $this->slotNumber,
                trim($this->vehicleNumber) ?: null,
            );
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        } catch (\Throwable $e) {
            Log::error('Parking assign failed', ['message' => $e->getMessage(), 'society_id' => $societyId]);
            $this->toastError(__('app.parking_assign_failed'));

            return;
        }

        $this->reset(['showAssign', 'allocateHouseId', 'slotNumber', 'vehicleNumber']);
        $this->toastSuccess(__('app.parking_allocated'));
    }

    public function release(int $slotId, ParkingService $service): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $slot = ParkingSlot::where('society_id', auth()->user()->society_id)->findOrFail($slotId);
        $service->release($slot);
        $this->toastSuccess(__('app.parking_released'));
    }

    public function render()
    {
        $societyId = (int) auth()->user()->society_id;

        abort_if(! $societyId, 403);

        return view('livewire.parking.index', [
            'slots' => ParkingSlot::query()
                ->where('society_id', $societyId)
                ->with(['activeAllocation.house'])
                ->orderBy('slot_number')
                ->get(),
            'houses' => House::where('society_id', $societyId)
                ->whereDoesntHave('parkingAllocations', fn ($q) => $q->whereNull('allocated_until'))
                ->orderBy('house_number')
                ->get(['id', 'house_number']),
            'isAdmin' => auth()->user()->isAdmin(),
        ])->layout('layouts.mobile', ['title' => __('app.parking')]);
    }
}
