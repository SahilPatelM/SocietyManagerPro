<?php

namespace App\Livewire\Houses;

use App\Repositories\Contracts\HouseRepositoryInterface;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(HouseRepositoryInterface $houses)
    {
        $societyId = auth()->user()->society_id;

        return view('livewire.houses.index', [
            'houses' => $houses->paginate($societyId, $this->search ?: null),
            'counts' => $houses->counts($societyId),
        ])->layout('layouts.mobile', ['title' => __('app.houses')]);
    }
}
