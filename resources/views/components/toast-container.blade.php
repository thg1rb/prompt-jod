<div class="fixed top-4 right-4 z-[60] flex flex-col gap-2 pointer-events-none">
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div
            x-data="{ visible: false }"
            x-init="setTimeout(() => { visible = true }, 10)"
            x-show="visible"
            @toast-hide.window="$event.detail === toast.id ? visible = false : null"
            @after-leave="$store.toast.remove(toast.id)"
            x-transition:enter="transition-all duration-300 ease-out"
            x-transition:enter-start="opacity-0 translate-x-4 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition-all duration-300 ease-in"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-4 scale-95"
            class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-lg shadow-elevated min-w-[300px] max-w-md"
            :class="{
                'bg-primary text-white': toast.type === 'success',
                'bg-destructive text-white': toast.type === 'error'
            }"
        >
            <span class="flex-shrink-0 mt-0.5" x-text="toast.type === 'success' ? '✓' : '✕'"></span>

            <span class="flex-1 text-sm font-medium" x-text="toast.message"></span>

            <button
                @click="window.dispatchEvent(new CustomEvent('toast-hide', { detail: toast.id }))"
                class="flex-shrink-0 ml-2 opacity-70 hover:opacity-100 transition-opacity"
            >
                ×
            </button>
        </div>
    </template>
</div>

