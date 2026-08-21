<?php

namespace App\Livewire\Announcements;

use App\Livewire\Concerns\ShowsToast;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use ShowsToast;
    use WithPagination;

    public bool $showForm = false;

    public string $title = '';

    public string $description = '';

    public bool $isEmergency = false;

    public function publish(AnnouncementService $service): void
    {
        $this->validate([
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        abort_unless(auth()->user()->isAdmin(), 403);

        $service->publish(auth()->user(), [
            'title' => $this->title,
            'description' => $this->description,
            'is_emergency' => $this->isEmergency,
        ]);

        $this->reset(['title', 'description', 'isEmergency', 'showForm']);
        $this->toastSuccess(__('app.announcement_published'));
    }

    public function render()
    {
        $announcements = Announcement::with('creator')
            ->where('society_id', auth()->user()->society_id)
            ->whereNotNull('sent_at')
            ->latest('sent_at')
            ->paginate(10);

        return view('livewire.announcements.index', [
            'announcements' => $announcements,
            'isAdmin' => auth()->user()->isAdmin(),
        ])->layout('layouts.mobile', ['title' => __('app.announcements')]);
    }
}
