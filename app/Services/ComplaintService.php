<?php

namespace App\Services;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\User;
use App\Services\Notification\FirebaseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComplaintService
{
    public function __construct(
        protected FirebaseService $firebase,
    ) {}

    public function submit(User $user, array $data, ?UploadedFile $image = null): Complaint
    {
        return DB::transaction(function () use ($user, $data, $image) {
            $complaint = Complaint::create([
                'society_id' => $user->society_id,
                'user_id' => $user->id,
                'house_id' => $data['house_id'] ?? $user->house_id,
                'complaint_number' => $this->generateNumber(),
                'category' => $data['category'],
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => ComplaintStatus::Pending->value,
            ]);

            if ($image) {
                $this->storeImage($complaint, $image);
            }

            $this->notifyAllMembers($complaint, $user->id);

            return $complaint->load(['attachments', 'user', 'house']);
        });
    }

    public function notifyAllMembers(Complaint $complaint, ?int $exceptUserId = null): void
    {
        $reporter = $complaint->user?->name ?? __('app.a_member');

        User::where('society_id', $complaint->society_id)
            ->where('status', 'active')
            ->when($exceptUserId, fn ($q) => $q->where('id', '!=', $exceptUserId))
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['member', 'society_admin', 'treasurer']))
            ->each(function (User $member) use ($complaint, $reporter) {
                $this->firebase->sendToUser(
                    $member,
                    __('app.complaint_new_title'),
                    __('app.complaint_new_body', [
                        'reporter' => $reporter,
                        'title' => $complaint->title,
                        'number' => $complaint->complaint_number,
                    ]),
                    'complaint_new',
                    [
                        'complaint_id' => $complaint->id,
                        'complaint_number' => $complaint->complaint_number,
                    ]
                );
            });
    }

    protected function storeImage(Complaint $complaint, UploadedFile $image): void
    {
        $path = $image->store("societies/{$complaint->society_id}/complaints", 'public');

        $complaint->attachments()->create([
            'file_path' => $path,
            'file_type' => $image->getClientMimeType(),
        ]);
    }

    protected function generateNumber(): string
    {
        return 'CMP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }
}
