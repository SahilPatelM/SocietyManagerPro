<?php

namespace App\Services;

use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;

class VisitorService
{
    public function checkIn(User $user, array $data): Visitor
    {
        return DB::transaction(function () use ($user, $data) {
            return Visitor::create([
                'society_id' => $user->society_id,
                'house_id' => $data['house_id'],
                'visitor_name' => $data['visitor_name'],
                'mobile' => $data['mobile'] ?? null,
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'entry_time' => now(),
                'logged_by' => $user->id,
            ]);
        });
    }

    public function checkOut(Visitor $visitor): Visitor
    {
        $visitor->update(['exit_time' => now()]);

        return $visitor->fresh(['house']);
    }
}
