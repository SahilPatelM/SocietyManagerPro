<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Livewire\Component;

class Index extends Component
{
    public array $stats = [];

    public function mount(DashboardService $dashboard): void
    {
        if (! auth()->user()?->society_id) {
            return;
        }

        $this->stats = $dashboard->stats(auth()->user()->society_id);
    }

    public function render()
    {
        return view('livewire.dashboard.index')
            ->layout('layouts.mobile', ['title' => __('app.dashboard')]);
    }
}
