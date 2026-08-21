<?php

namespace App\Livewire\Complaints;

use App\Livewire\Concerns\ShowsToast;
use App\Models\Complaint;
use App\Services\ComplaintService;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use ShowsToast;
    use WithFileUploads;
    use WithPagination;

    /** @var list<string> */
    public const CATEGORIES = ['water', 'electricity', 'security', 'parking', 'cleaning', 'other'];

    public string $category = 'water';

    public string $title = '';

    public string $description = '';

    public $photo = null;

    public function submit(ComplaintService $service): void
    {
        $this->validate([
            'category' => 'required|in:'.implode(',', self::CATEGORIES),
            'title' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10|max:5000',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $service->submit(auth()->user(), [
            'category' => $this->category,
            'title' => $this->title,
            'description' => $this->description,
        ], $this->photo);

        $this->reset(['title', 'description', 'photo']);
        $this->resetValidation();
        $this->toastSuccess(__('app.complaint_submitted'));
    }

    public function render()
    {
        $query = Complaint::with(['attachments', 'user', 'house'])
            ->where('society_id', auth()->user()->society_id);

        if (auth()->user()->hasRole('member')) {
            $query->where('user_id', auth()->id());
        }

        return view('livewire.complaints.index', [
            'complaints' => $query->latest()->paginate(10),
            'categories' => self::CATEGORIES,
        ])->layout('layouts.mobile', ['title' => __('app.complaints')]);
    }
}
