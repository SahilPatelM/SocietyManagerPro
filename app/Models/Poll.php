<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    protected $fillable = [
        'society_id', 'created_by', 'title', 'description',
        'status', 'ends_at', 'published_at', 'notifications_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
            'notifications_sent_at' => 'datetime',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return $this->published_at !== null;
    }

    public function totalVotes(): int
    {
        return (int) $this->options()->sum('votes_count');
    }
}
