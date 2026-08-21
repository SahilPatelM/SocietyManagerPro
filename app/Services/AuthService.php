<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function sendOtp(string $mobile): OtpVerification
    {
        $otp = app()->environment('local') ? '123456' : (string) random_int(100000, 999999);

        OtpVerification::where('mobile', $mobile)->update(['is_used' => true]);

        return OtpVerification::create([
            'mobile' => $mobile,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public function verifyOtp(string $mobile, string $otp): ?User
    {
        $record = OtpVerification::where('mobile', $mobile)
            ->where('otp', $otp)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record) {
            return null;
        }

        $record->update(['is_used' => true]);

        $user = User::where('mobile', $mobile)->first();

        if ($user) {
            $user->update(['mobile_verified_at' => now()]);
        }

        return $user;
    }

    public function loginWithPassword(string $mobile, string $password): ?User
    {
        $user = User::where('mobile', $mobile)->first();

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function resetPassword(string $mobile, string $otp, string $password): bool
    {
        $user = $this->verifyOtp($mobile, $otp);

        if (! $user) {
            return false;
        }

        $user->update(['password' => Hash::make($password)]);

        return true;
    }

    public function createToken(User $user): string
    {
        $user->tokens()->delete();

        return $user->createToken('mobile-app', ['*'])->plainTextToken;
    }
}
