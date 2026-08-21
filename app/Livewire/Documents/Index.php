<?php

namespace App\Livewire\Documents;

use App\Livewire\Concerns\ShowsToast;
use App\Models\Document;
use App\Services\DocumentService;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use ShowsToast;
    use WithFileUploads;
    use WithPagination;

    public bool $showForm = false;

    public string $title = '';

    public string $category = 'rules';

    public $file = null;

    /** @var list<string> */
    public const CATEGORIES = ['rules', 'meeting_minutes', 'agm', 'audit', 'receipt', 'other'];

    public function upload(DocumentService $service): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->validate([
            'title' => 'required|string|min:3|max:255',
            'category' => 'required|in:'.implode(',', self::CATEGORIES),
            'file' => 'required|file|max:10240|mimes:pdf,jpeg,jpg,png,doc,docx',
        ]);

        $service->upload(auth()->user(), [
            'title' => $this->title,
            'category' => $this->category,
        ], $this->file);

        $this->reset(['title', 'file', 'showForm']);
        $this->toastSuccess(__('app.document_uploaded'));
    }

    public function render()
    {
        return view('livewire.documents.index', [
            'documents' => Document::with('uploader')
                ->where('society_id', auth()->user()->society_id)
                ->latest()
                ->paginate(15),
            'isAdmin' => auth()->user()->isAdmin(),
        ])->layout('layouts.mobile', ['title' => __('app.documents')]);
    }
}
