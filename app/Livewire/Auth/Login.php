<?php

namespace App\Livewire\Auth;

use App\Services\AuthService;
use Livewire\Component;

class Login extends Component
{
    public string $mode = 'otp';

    public string $mobile = '';

    public string $otp = '';

    public string $password = '';

    public ?string $message = null;

    public bool $otpSent = false;

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
        $this->resetOtp();
        $this->resetErrorBag();
    }

    public function resetOtp(): void
    {
        $this->otpSent = false;
        $this->otp = '';
        $this->message = null;
    }

    public function sendOtp(AuthService $auth): void
    {
        $this->validate(['mobile' => 'required|min:10']);
        $auth->sendOtp($this->mobile);
        $this->otpSent = true;
        $this->message = __('auth.otp_sent').(app()->environment('local') ? ' · OTP: 123456' : '');
    }

    public function verifyOtp(AuthService $auth): void
    {
        $user = $auth->verifyOtp($this->mobile, $this->otp);

        if (! $user) {
            $this->addError('otp', __('auth.invalid_otp'));

            return;
        }

        auth()->login($user, true);
        session(['locale' => $user->locale]);
        $this->redirect(route('dashboard'), navigate: true);
    }

    public function loginPassword(AuthService $auth): void
    {
        $user = $auth->loginWithPassword($this->mobile, $this->password);

        if (! $user) {
            $this->addError('password', __('auth.failed'));

            return;
        }

        auth()->login($user, true);
        session(['locale' => $user->locale]);
        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.login');
    }
}
