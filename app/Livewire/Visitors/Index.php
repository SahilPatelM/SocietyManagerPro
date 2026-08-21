<?php

namespace App\Livewire\Visitors;

use App\Livewire\Concerns\ShowsToast;
use App\Models\House;
use App\Models\Visitor;
use App\Services\VisitorService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use ShowsToast;
    use WithPagination;

    public bool $showForm = false;

    public ?int $houseId = null;

    public string $visitorName = '';

    public string $mobile = '';

    public string $vehicleNumber = '';

    public string $filter = 'inside';

    public function mount(): void
    {
        if (auth()->user()->house_id) {
            $this->houseId = auth()->user()->house_id;
        }
    }

    public function checkIn(VisitorService $service): void
    {
        $this->validate([
            'houseId' => 'required|exists:houses,id',
            'visitorName' => 'required|string|min:2|max:255',
            'mobile' => 'nullable|string|max:15',
            'vehicleNumber' => 'nullable|string|max:50',
        ]);

        $service->checkIn(auth()->user(), [
            'house_id' => $this->houseId,
            'visitor_name' => $this->visitorName,
            'mobile' => $this->mobile ?: null,
            'vehicle_number' => $this->vehicleNumber ?: null,
        ]);

        $this->reset(['visitorName', 'mobile', 'vehicleNumber', 'showForm']);
        $this->toastSuccess(__('app.visitor_checked_in'));
    }

    public function checkOut(int $visitorId, VisitorService $service): void
    {
        $visitor = $this->findVisitor($visitorId);
        $service->checkOut($visitor);
        $this->toastSuccess(__('app.visitor_checked_out'));
    }

    public function render()
    {
        $user = auth()->user();
        $query = Visitor::with('house')
            ->where('society_id', $user->society_id);

        if ($user->hasRole('member')) {
            $query->where('house_id', $user->house_id);
        }

        if ($this->filter === 'inside') {
            $query->whereNull('exit_time');
        }

        $houses = House::where('society_id', $user->society_id)
            ->orderBy('house_number')
            ->get(['id', 'house_number']);

        return view('livewire.visitors.index', [
            'visitors' => $query->latest('entry_time')->paginate(15),
            'houses' => $houses,
            'canSelectHouse' => $user->isAdmin(),
        ])->layout('layouts.mobile', ['title' => __('app.visitors')]);
    }

    protected function findVisitor(int $id): Visitor
    {
        $visitor = Visitor::where('society_id', auth()->user()->society_id)->findOrFail($id);

        if (auth()->user()->hasRole('member')) {
            abort_unless($visitor->house_id === auth()->user()->house_id, 403);
        }

        return $visitor;
    }
}
