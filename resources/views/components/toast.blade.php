<div
    x-data="{ visible: false, type: 'success', message: '', id: '' }"
    x-init="setTimeout(() => visible = true, 10)"
    x-show="visible"
    @toast-hide.window="$event.detail === id ? visible = false : null"
    @after-leave="$store.toast.remove(id)"
    x-transition:enter="transition-all duration-300 ease-out"
    x-transition:enter-start="opacity-0 translate-x-4 scale-95"
    x-transition:enter-end="opacity-100 translate-x-0 scale-100"
    x-transition:leave="transition-all duration-200 ease-in"
    x-transition:leave-start="opacity-100 translate-x-0 scale-100"
    x-transition:leave-end="opacity-0 translate-x-4 scale-95"
    class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg shadow-elevated min-w-[300px] max-w-md"
    :class="{
        'bg-primary text-white': type === 'success',
        'bg-destructive text-white': type === 'error'
    }"
>
    <span class="flex-shrink-0" x-text="type === 'success' ? '✓' : '✕'"></span>

    <span class="flex-1 text-sm font-medium" x-text="message"></span>

    <button
        @click="$store.toast.remove(id)"
        class="flex-shrink-0 ml-2 opacity-70 hover:opacity-100 transition-opacity"
    >
        ×
    </button>
</div>
