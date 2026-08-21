@php
    $initial = array_values(array_filter([
        session('success') ? ['id' => uniqid('t'), 'type' => 'success', 'message' => session('success')] : null,
        session('error') ? ['id' => uniqid('t'), 'type' => 'error', 'message' => session('error')] : null,
        session('warning') ? ['id' => uniqid('t'), 'type' => 'warning', 'message' => session('warning')] : null,
        is_array(session('toast')) ? [
            'id' => uniqid('t'),
            'type' => session('toast')['type'] ?? 'success',
            'message' => session('toast')['message'] ?? '',
        ] : null,
    ]));
@endphp

<div
    class="toast-stack"
    aria-live="polite"
    aria-atomic="true"
    x-data="toastStack(@js($initial))"
    x-init="$nextTick(() => items.forEach(t => setTimeout(() => remove(t.id), 4500)))"
    @toast.window="add($event.detail.type, $event.detail.message)"
>
    <template x-for="toast in items" :key="toast.id">
        <div
            class="toast-item"
            :class="'toast-' + toast.type"
            x-show="toast.visible"
            x-transition:enter="toast-enter"
            x-transition:enter-start="toast-enter-start"
            x-transition:enter-end="toast-enter-end"
            x-transition:leave="toast-leave"
            x-transition:leave-start="toast-leave-start"
            x-transition:leave-end="toast-leave-end"
            role="alert"
        >
            <span class="toast-icon" x-show="toast.type === 'success'">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <span class="toast-icon" x-show="toast.type === 'error'">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </span>
            <span class="toast-icon" x-show="toast.type === 'warning'">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-8.25.75a8.25 8.25 0 1116.5 0 8.25 8.25 0 01-16.5 0zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </span>
            <p class="toast-message" x-text="toast.message"></p>
            <button type="button" class="toast-close" @click="remove(toast.id)" aria-label="Close">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>
