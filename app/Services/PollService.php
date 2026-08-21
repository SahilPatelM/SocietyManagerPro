<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\User;
use App\Services\Notification\FirebaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PollService
{
    public function __construct(
        protected FirebaseService $firebase,
    ) {}

    public function publish(User $author, array $data): Poll
    {
        return DB::transaction(function () use ($author, $data) {
            $poll = Poll::create([
                'society_id' => $author->society_id,
                'created_by' => $author->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => 'active',
                'ends_at' => $data['ends_at'] ?? null,
                'published_at' => now(),
                'notifications_sent_at' => now(),
            ]);

            foreach ($data['options'] as $index => $label) {
                PollOption::create([
                    'poll_id' => $poll->id,
                    'label' => trim($label),
                    'sort_order' => $index,
                ]);
            }

            $this->notifySociety($poll, $author->id);

            return $poll->load(['options', 'creator']);
        });
    }

    public function vote(User $user, Poll $poll, int $optionId): PollVote
    {
        abort_unless($poll->society_id === $user->society_id, 403);

        if (! $poll->isOpen()) {
            throw ValidationException::withMessages([
                'vote' => __('app.poll_closed'),
            ]);
        }

        if (PollVote::where('poll_id', $poll->id)->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'vote' => __('app.poll_already_voted'),
            ]);
        }

        $option = PollOption::where('poll_id', $poll->id)->findOrFail($optionId);

        return DB::transaction(function () use ($poll, $option, $user) {
            $vote = PollVote::create([
                'poll_id' => $poll->id,
                'poll_option_id' => $option->id,
                'user_id' => $user->id,
            ]);

            $option->increment('votes_count');

            return $vote;
        });
    }

    public function close(Poll $poll): Poll
    {
        $poll->update(['status' => 'closed']);

        return $poll->fresh(['options']);
    }

    protected function notifySociety(Poll $poll, int $exceptUserId): void
    {
        User::where('society_id', $poll->society_id)
            ->where('status', 'active')
            ->where('id', '!=', $exceptUserId)
            ->each(function (User $member) use ($poll) {
                $this->firebase->sendToUser(
                    $member,
                    __('app.poll_new_title'),
                    __('app.poll_new_body', ['title' => $poll->title]),
                    'poll_new',
                    ['poll_id' => $poll->id]
                );
            });
    }
}
