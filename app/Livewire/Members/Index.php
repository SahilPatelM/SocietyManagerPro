<?php

namespace App\Livewire\Members;

use App\Repositories\Contracts\MemberRepositoryInterface;
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

    public function render(MemberRepositoryInterface $members)
    {
        return view('livewire.members.index', [
            'members' => $members->paginate(
                auth()->user()->society_id,
                $this->search ?: null
            ),
        ])->layout('layouts.mobile', ['title' => __('app.members')]);
    }
}
