<div class="fixed top-4 right-4 z-[60] flex flex-col gap-2.5 pointer-events-none max-w-[360px]">
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div
            x-data="{ visible: false }"
            x-init="setTimeout(() => { visible = true }, 10)"
            x-show="visible"
            @toast-hide.window="$event.detail === toast.id ? visible = false : null"
            @after-leave="$store.toast.remove(toast.id)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-4 scale-95"
            class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-2xl shadow-floating border border-border"
            :class="{
                'bg-card text-foreground': toast.type === 'success',
                'bg-card text-foreground': toast.type === 'error'
            }"
        >
            <template x-if="toast.type === 'success'">
                <div class="w-8 h-8 rounded-full bg-success flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-success-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </template>
            <template x-if="toast.type === 'error'">
                <div class="w-8 h-8 rounded-full bg-destructive flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-destructive-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </div>
            </template>

            <span class="flex-1 text-sm font-medium text-foreground" x-text="toast.message"></span>

            <button
                @click="window.dispatchEvent(new CustomEvent('toast-hide', { detail: toast.id }))"
                class="flex-shrink-0 w-6 h-6 rounded-full hover:bg-surface-subtle flex items-center justify-center transition-colors"
            >
                <svg class="w-3.5 h-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>