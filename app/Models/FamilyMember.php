<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyMember extends Model
{
    protected $fillable = ['user_id', 'name', 'relation', 'mobile', 'age'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
