<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementTarget;
use App\Models\User;
use App\Services\Notification\FirebaseService;
use Illuminate\Support\Facades\DB;

class AnnouncementService
{
    public function __construct(
        protected FirebaseService $firebase,
    ) {}

    public function publish(User $author, array $data): Announcement
    {
        return DB::transaction(function () use ($author, $data) {
            $announcement = Announcement::create([
                'society_id' => $author->society_id,
                'created_by' => $author->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'] ?? 'text',
                'is_emergency' => (bool) ($data['is_emergency'] ?? false),
                'sent_at' => now(),
            ]);

            AnnouncementTarget::create([
                'announcement_id' => $announcement->id,
                'target_type' => 'all',
            ]);

            $this->notifySociety($announcement);

            return $announcement->load('creator');
        });
    }

    public function notifySociety(Announcement $announcement): void
    {
        User::where('society_id', $announcement->society_id)
            ->where('status', 'active')
            ->where('id', '!=', $announcement->created_by)
            ->each(function (User $user) use ($announcement) {
                $this->firebase->sendToUser(
                    $user,
                    $announcement->is_emergency ? __('app.announcement_emergency_title') : __('app.announcement_new_title'),
                    __('app.announcement_new_body', ['title' => $announcement->title]),
                    'announcement',
                    ['announcement_id' => $announcement->id]
                );
            });
    }
}
