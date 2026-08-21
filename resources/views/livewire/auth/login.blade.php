<div class="login-screen">
    <section class="login-hero">
        <div class="login-hero-bg" aria-hidden="true"></div>
        <div class="login-hero-grid" aria-hidden="true"></div>

        <div class="login-hero-top">
            <button type="button" onclick="toggleDark()" class="login-icon-btn" aria-label="{{ __('app.dark_mode') }}">
                <x-ui.icon name="moon" class="h-5 w-5 dark:hidden" />
                <x-ui.icon name="sun" class="hidden h-5 w-5 dark:block" />
            </button>
            <a href="{{ route('locale.switch', app()->getLocale() === 'en' ? 'gu' : 'en') }}" class="login-icon-btn text-sm font-bold">
                {{ app()->getLocale() === 'en' ? 'ગુ' : 'EN' }}
            </a>
        </div>

        <div class="login-hero-content">
            <div class="login-logo-ring">
                <x-ui.icon name="building" class="h-10 w-10 text-white" />
            </div>
            <h1 class="login-brand">{{ __('app.app_name') }}</h1>
            <p class="login-tagline">{{ __('app.login') }}</p>
        </div>
    </section>

    <section class="login-panel">
        <div class="login-panel-handle" aria-hidden="true"></div>

        <div class="login-tabs">
            <button type="button" wire:click="setMode('otp')" class="login-tab {{ $mode === 'otp' ? 'active' : '' }}">
                <x-ui.icon name="phone" class="h-4 w-4" />
                {{ __('app.mobile_otp') }}
            </button>
            <button type="button" wire:click="setMode('password')" class="login-tab {{ $mode === 'password' ? 'active' : '' }}">
                <x-ui.icon name="sparkles" class="h-4 w-4" />
                {{ __('app.password_login') }}
            </button>
        </div>

        <div class="login-form">
            <div class="login-fields">
                <label class="login-label">Mobile Number</label>
                <div class="login-input-wrap">
                    <span class="login-input-prefix">+91</span>
                    <input
                        type="tel"
                        wire:model="mobile"
                        class="login-input"
                        placeholder="9876543210"
                        inputmode="numeric"
                        autocomplete="tel"
                    >
                </div>

                @if($mode === 'otp')
                    @if($message)
                        <p class="login-hint login-hint-success">{{ $message }}</p>
                    @endif

                    @if($otpSent)
                        <label class="login-label login-label-spaced">Enter OTP</label>
                        <input
                            type="text"
                            wire:model="otp"
                            class="login-field login-otp"
                            placeholder="000000"
                            maxlength="6"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                        >
                        @error('otp')
                            <p class="login-hint login-hint-error">{{ $message }}</p>
                        @enderror
                    @endif
                @else
                    <label class="login-label login-label-spaced">Password</label>
                    <div class="login-input-wrap">
                        <input
                            type="password"
                            wire:model="password"
                            class="login-input"
                            placeholder="Enter password"
                            autocomplete="current-password"
                        >
                    </div>
                    @error('password')
                        <p class="login-hint login-hint-error">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="login-actions">
                @if($mode === 'otp')
                    @if(! $otpSent)
                        <button wire:click="sendOtp" wire:loading.attr="disabled" class="login-submit">
                            <span wire:loading.remove wire:target="sendOtp">{{ __('app.send_otp') }}</span>
                            <span wire:loading wire:target="sendOtp" class="login-loading">Sending…</span>
                        </button>
                    @else
                        <button wire:click="verifyOtp" wire:loading.attr="disabled" class="login-submit">
                            <span wire:loading.remove wire:target="verifyOtp">{{ __('app.verify') }}</span>
                            <span wire:loading wire:target="verifyOtp" class="login-loading">Verifying…</span>
                        </button>
                        <button type="button" wire:click="resetOtp" class="login-link-btn">Change number / Resend</button>
                    @endif
                @else
                    <button wire:click="loginPassword" wire:loading.attr="disabled" class="login-submit">
                        <span wire:loading.remove wire:target="loginPassword">{{ __('app.login') }}</span>
                        <span wire:loading wire:target="loginPassword" class="login-loading">Signing in…</span>
                    </button>
                    <button type="button" class="login-link-btn">{{ __('app.forgot_password') }}</button>
                @endif

                @if(app()->environment('local'))
                    <p class="login-demo">Demo 9876543210 · password</p>
                @endif
            </div>
        </div>
    </section>
</div>
