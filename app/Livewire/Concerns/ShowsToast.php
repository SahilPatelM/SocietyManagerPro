<?php

namespace App\Livewire\Concerns;

trait ShowsToast
{
    protected function toast(string $type, string $message): void
    {
        $this->dispatch('toast', type: $type, message: $message);
    }

    protected function toastSuccess(string $message): void
    {
        $this->toast('success', $message);
    }

    protected function toastError(string $message): void
    {
        $this->toast('error', $message);
    }
}
