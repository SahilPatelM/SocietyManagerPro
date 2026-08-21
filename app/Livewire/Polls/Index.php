<?php

namespace App\Livewire\Polls;

use App\Livewire\Concerns\ShowsToast;
use App\Models\Poll;
use App\Models\PollVote;
use App\Services\PollService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use ShowsToast;
    use WithPagination;

    public bool $showForm = false;

    public string $title = '';

    public string $description = '';

    public string $option1 = '';

    public string $option2 = '';

    public string $option3 = '';

    public string $option4 = '';

    public ?string $endsAt = null;

    public function publish(PollService $service): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $options = array_values(array_filter([
            trim($this->option1),
            trim($this->option2),
            trim($this->option3),
            trim($this->option4),
        ]));

        $this->validate([
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:5000',
            'endsAt' => 'nullable|date',
        ]);

        if (count($options) < 2) {
            $this->addError('option1', __('app.poll_min_options'));

            return;
        }

        $service->publish(auth()->user(), [
            'title' => $this->title,
            'description' => $this->description,
            'ends_at' => $this->endsAt,
            'options' => $options,
        ]);

        $this->reset(['title', 'description', 'option1', 'option2', 'option3', 'option4', 'endsAt', 'showForm']);
        $this->toastSuccess(__('app.poll_published'));
    }

    public function vote(int $pollId, int $optionId, PollService $service): void
    {
        $poll = Poll::where('society_id', auth()->user()->society_id)->findOrFail($pollId);

        try {
            $service->vote(auth()->user(), $poll, $optionId);
            $this->toastSuccess(__('app.poll_vote_recorded'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->toastError($e->validator->errors()->first());
        }
    }

    public function closePoll(int $pollId, PollService $service): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $poll = Poll::where('society_id', auth()->user()->society_id)->findOrFail($pollId);
        $service->close($poll);
        $this->toastSuccess(__('app.poll_closed_success'));
    }

    public function render()
    {
        $user = auth()->user();
        $userId = $user->id;

        $polls = Poll::with(['options', 'creator'])
            ->where('society_id', $user->society_id)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->paginate(8);

        $userVotes = PollVote::where('user_id', $userId)
            ->whereIn('poll_id', $polls->pluck('id'))
            ->pluck('poll_option_id', 'poll_id');

        return view('livewire.polls.index', [
            'polls' => $polls,
            'userVotes' => $userVotes,
            'isAdmin' => $user->isAdmin(),
        ])->layout('layouts.mobile', ['title' => __('app.polls')]);
    }
}
