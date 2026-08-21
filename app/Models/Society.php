<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Society extends Model
{
    protected $fillable = [
        'name', 'address', 'city', 'registration_number',
        'opening_balance', 'logo', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }

    public function houses(): HasMany
    {
        return $this->hasMany(House::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }
}
