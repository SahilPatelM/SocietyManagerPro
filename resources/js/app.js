import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    initPageAnimations();
    initDarkMode();
    initLivewireToasts();
    initPwa();
});

function initPwa() {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }
}

window.pwaInstall = function () {
    const DISMISS_KEY = 'smp-pwa-dismiss';
    const DISMISS_DAYS = 7;

    return {
        canNativeInstall: false,
        isIos: false,
        iosNotSafari: false,
        isStandalone: false,
        showBanner: false,
        showIosGuide: false,
        deferredPrompt: null,

        init() {
            this.isStandalone =
                window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone === true;

            if (this.isStandalone) {
                return;
            }

            const ua = navigator.userAgent;
            this.isIos =
                /iPad|iPhone|iPod/.test(ua) ||
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            this.iosNotSafari = this.isIos && /CriOS|FxiOS|EdgiOS|OPiOS/.test(ua);

            window.addEventListener('beforeinstallprompt', (event) => {
                event.preventDefault();
                this.deferredPrompt = event;
                this.canNativeInstall = true;
                this.updateBanner();
            });

            window.addEventListener('appinstalled', () => {
                this.canNativeInstall = false;
                this.showBanner = false;
                this.deferredPrompt = null;
            });

            this.updateBanner();
        },

        updateBanner() {
            if (this.isStandalone || this.isDismissed()) {
                this.showBanner = false;
                return;
            }

            this.showBanner = this.canNativeInstall || this.isIos;
        },

        isDismissed() {
            const raw = localStorage.getItem(DISMISS_KEY);
            if (!raw) {
                return false;
            }

            const dismissedAt = Number(raw);
            if (Number.isNaN(dismissedAt)) {
                return false;
            }

            const ms = DISMISS_DAYS * 24 * 60 * 60 * 1000;
            return Date.now() - dismissedAt < ms;
        },

        async install() {
            if (this.deferredPrompt) {
                this.deferredPrompt.prompt();
                const { outcome } = await this.deferredPrompt.userChoice;
                this.deferredPrompt = null;
                this.canNativeInstall = false;

                if (outcome === 'accepted') {
                    this.showBanner = false;
                }

                return;
            }

            if (this.isIos) {
                this.showIosGuide = true;
            }
        },

        dismiss() {
            localStorage.setItem(DISMISS_KEY, String(Date.now()));
            this.showBanner = false;
        },
    };
};

function initPageAnimations() {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.08, rootMargin: '0px 0px -40px 0px' }
    );

    document.querySelectorAll('[data-animate]').forEach((el) => observer.observe(el));
}

function initDarkMode() {
    const stored = localStorage.getItem('smp-dark');
    if (stored === '1') {
        document.documentElement.classList.add('dark');
    } else if (stored === '0') {
        document.documentElement.classList.remove('dark');
    }
}

window.toggleDark = function () {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('smp-dark', isDark ? '1' : '0');

    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.content = isDark ? '#0c0a1f' : '#6366f1';
    }

    fetch('/dark-toggle', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
    });
};

window.toastStack = function (initial = []) {
    return {
        items: (initial || []).map((t) => ({ ...t, visible: true })),
        add(type, message) {
            if (!message) return;
            const id = `t-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
            this.items.push({ id, type: type || 'success', message, visible: true });
            setTimeout(() => this.remove(id), 4500);
        },
        remove(id) {
            const toast = this.items.find((t) => t.id === id);
            if (!toast) return;
            toast.visible = false;
            setTimeout(() => {
                this.items = this.items.filter((t) => t.id !== id);
            }, 320);
        },
    };
};

window.showToast = function (type, message) {
    window.dispatchEvent(new CustomEvent('toast', { detail: { type, message } }));
};

function initLivewireToasts() {
    document.addEventListener('livewire:init', () => {
        if (typeof Livewire === 'undefined') return;

        Livewire.on('toast', ({ type, message }) => {
            window.showToast(type, message);
        });
    });
}

document.addEventListener('livewire:navigated', () => {
    initPageAnimations();
});
