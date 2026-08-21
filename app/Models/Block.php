<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    protected $fillable = ['society_id', 'name', 'code'];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function houses(): HasMany
    {
        return $this->hasMany(House::class);
    }
}
