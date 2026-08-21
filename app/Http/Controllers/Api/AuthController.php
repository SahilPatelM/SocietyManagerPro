<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthService $auth) {}

    public function sendOtp(OtpRequest $request): JsonResponse
    {
        $this->auth->sendOtp($request->mobile);

        return response()->json([
            'message' => __('auth.otp_sent'),
            'debug_otp' => app()->environment('local') ? '123456' : null,
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $user = $this->auth->verifyOtp($request->mobile, $request->otp);

        if (! $user) {
            return response()->json(['message' => __('auth.invalid_otp')], 422);
        }

        return response()->json([
            'user' => new UserResource($user),
            'token' => $this->auth->createToken($user),
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->auth->loginWithPassword($request->mobile, $request->password);

        if (! $user) {
            return response()->json(['message' => __('auth.failed')], 401);
        }

        return response()->json([
            'user' => new UserResource($user),
            'token' => $this->auth->createToken($user),
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        if (! $this->auth->resetPassword($request->mobile, $request->otp, $request->password)) {
            return response()->json(['message' => __('auth.invalid_otp')], 422);
        }

        return response()->json(['message' => __('auth.password_reset')]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load(['house', 'familyMembers', 'vehicles', 'roles']));
    }

    public function updateProfile(Request $request): UserResource
    {
        $user = $request->user();
        $user->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'locale' => 'nullable|in:en,gu',
            'emergency_contact_name' => 'nullable|string',
            'emergency_mobile' => 'nullable|string|max:15',
        ]));

        return new UserResource($user->fresh(['house', 'familyMembers', 'vehicles']));
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->user()->update(['fcm_token' => $request->validate(['fcm_token' => 'required|string'])['fcm_token']]);

        return response()->json(['message' => 'FCM token updated']);
    }
}
