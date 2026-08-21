<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    public function sendToUser(User $user, string $title, string $body, string $type, array $data = []): void
    {
        UserNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data,
            'delivery_status' => 'pending',
        ]);

        if (! $user->fcm_token || ! config('services.firebase.server_key')) {
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key='.config('services.firebase.server_key'),
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $user->fcm_token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_merge($data, ['type' => $type]),
            ]);

            UserNotification::where('user_id', $user->id)
                ->latest()
                ->first()
                ?->update(['delivery_status' => $response->successful() ? 'sent' : 'failed']);
        } catch (\Throwable $e) {
            Log::error('FCM failed: '.$e->getMessage());
        }
    }

    public function sendToSociety(int $societyId, string $title, string $body, string $type): void
    {
        User::where('society_id', $societyId)
            ->whereNotNull('fcm_token')
            ->each(fn (User $user) => $this->sendToUser($user, $title, $body, $type));
    }
}
