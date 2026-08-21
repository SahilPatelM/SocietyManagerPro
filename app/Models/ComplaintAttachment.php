<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ComplaintAttachment extends Model
{
    protected $fillable = ['complaint_id', 'file_path', 'file_type'];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => Storage::disk('public')->url($this->file_path));
    }
}
