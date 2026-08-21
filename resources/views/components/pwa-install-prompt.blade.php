<div
    x-data="pwaInstall()"
    x-cloak
    @pwa-install-request.window="install()"
    class="pwa-install-root"
>
    {{-- Android / Chrome: one-click install banner --}}
    <div
        x-show="showBanner && canNativeInstall"
        x-transition
        class="pwa-install-banner"
        role="region"
        aria-label="{{ __('app.pwa_install_title') }}"
    >
        <img src="{{ asset('icons/icon-192.png') }}" alt="" class="pwa-install-icon" width="44" height="44">
        <div class="min-w-0 flex-1">
            <p class="pwa-install-title">{{ __('app.pwa_install_title') }}</p>
            <p class="pwa-install-text">{{ __('app.pwa_install_android_hint') }}</p>
        </div>
        <button type="button" class="pwa-install-btn" @click="install()">
            {{ __('app.pwa_install_button') }}
        </button>
        <button type="button" class="pwa-install-dismiss" @click="dismiss()" aria-label="{{ __('app.pwa_install_later') }}">
            ×
        </button>
    </div>

    {{-- iOS Safari: tap opens step guide (Apple does not allow one-click install) --}}
    <div
        x-show="showBanner && isIos && !canNativeInstall"
        x-transition
        class="pwa-install-banner"
        role="region"
        aria-label="{{ __('app.pwa_install_title') }}"
    >
        <img src="{{ asset('icons/icon-192.png') }}" alt="" class="pwa-install-icon" width="44" height="44">
        <div class="min-w-0 flex-1">
            <p class="pwa-install-title">{{ __('app.pwa_install_title') }}</p>
            <p class="pwa-install-text">{{ __('app.pwa_install_ios_hint') }}</p>
        </div>
        <button type="button" class="pwa-install-btn" @click="install()">
            {{ __('app.pwa_install_how') }}
        </button>
        <button type="button" class="pwa-install-dismiss" @click="dismiss()" aria-label="{{ __('app.pwa_install_later') }}">
            ×
        </button>
    </div>

    {{-- iOS guide modal --}}
    <div
        x-show="showIosGuide"
        x-transition.opacity
        class="pwa-install-overlay"
        @click.self="showIosGuide = false"
    >
        <div class="pwa-install-modal" role="dialog" aria-modal="true" aria-labelledby="pwa-ios-title">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p id="pwa-ios-title" class="text-lg font-bold">{{ __('app.pwa_install_title') }}</p>
                    <p class="mt-1 text-sm" style="color:var(--muted)">{{ __('app.pwa_install_ios_modal_sub') }}</p>
                </div>
                <button type="button" class="pwa-install-dismiss" @click="showIosGuide = false">×</button>
            </div>

            <ol class="pwa-install-steps">
                <li>
                    <span class="pwa-step-num">1</span>
                    <span x-show="iosNotSafari">{{ __('app.pwa_ios_step_safari') }}</span>
                    <span x-show="!iosNotSafari">{{ __('app.pwa_ios_step_share') }}</span>
                </li>
                <li x-show="!iosNotSafari">
                    <span class="pwa-step-num">2</span>
                    <span>{{ __('app.pwa_ios_step_add') }}</span>
                </li>
                <li x-show="!iosNotSafari">
                    <span class="pwa-step-num">3</span>
                    <span>{{ __('app.pwa_ios_step_confirm') }}</span>
                </li>
            </ol>

            <button type="button" class="btn-primary mt-4 w-full" @click="showIosGuide = false">
                {{ __('app.pwa_install_got_it') }}
            </button>
        </div>
    </div>
</div>
