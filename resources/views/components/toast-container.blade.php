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
                'bg-primary text-primary-foreground': toast.type === 'success',
                'bg-destructive text-destructive-foreground': toast.type === 'error'
            }"
        >
            <template x-if="toast.type === 'success'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 flex-shrink-0" viewBox="0 0 50 50" fill="currentColor">
                    <path d="M25,2C12.318,2,2,12.318,2,25c0,12.683,10.318,23,23,23c12.683,0,23-10.317,23-23C48,12.318,37.683,2,25,2z M35.827,16.562 L24.316,33.525l-8.997-8.349c-0.405-0.375-0.429-1.008-0.053-1.413c0.375-0.406,1.009-0.428,1.413-0.053l7.29,6.764l10.203-15.036 c0.311-0.457,0.933-0.575,1.389-0.266C36.019,15.482,36.138,16.104,35.827,16.562z"></path>
                </svg>
            </template>
            <template x-if="toast.type === 'error'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 flex-shrink-0" viewBox="0 0 50 50" fill="currentColor">
                    <path d="M25,2C12.318,2,2,12.318,2,25c0,12.683,10.318,23,23,23c12.683,0,23-10.317,23-23C48,12.318,37.683,2,25,2z M16.5,16.5l17,17 M33.5,16.5l-17,17" stroke="currentColor" stroke-width="4" fill="none" stroke-linecap="round"></path>
                </svg>
            </template>

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

