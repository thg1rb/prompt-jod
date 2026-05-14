@props(['size' => 'default'])

@php
    $sizeClasses = match($size) {
        'sm' => 'h-8 w-8',
        'default' => 'h-9 w-9',
        'lg' => 'h-10 w-10',
        default => 'h-9 w-9'
    };
    $iconClasses = match($size) {
        'sm' => 'h-[18px] w-[18px]',
        'default' => 'h-[18px] w-[18px]',
        'lg' => 'h-5 w-5',
        default => 'h-[18px] w-[18px]'
    };
@endphp

<button
    type="button"
    @click="$store.theme.toggle()"
    class="{{ $sizeClasses }} relative inline-flex items-center justify-center rounded-xl text-foreground hover:bg-surface-subtle active:bg-surface-elevated transition-colors focus:outline-none focus:ring-2 focus:ring-ring overflow-hidden"
    aria-label="เปลี่ยนธีม"
>
    <!-- Sun icon (shown in dark mode) -->
    <svg
        x-show="$store.theme.isDark"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-50 rotate-180"
        x-transition:enter-end="opacity-100 scale-100 rotate-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 rotate-0"
        x-transition:leave-end="opacity-0 scale-50 -rotate-90"
        class="{{ $iconClasses }} absolute"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        style="display: none;"
    >
        <circle cx="12" cy="12" r="4"/>
        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
    </svg>

    <!-- Moon icon (shown in light mode) -->
    <svg
        x-show="!$store.theme.isDark"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-50 rotate-90"
        x-transition:enter-end="opacity-100 scale-100 rotate-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 rotate-0"
        x-transition:leave-end="opacity-0 scale-50 rotate-180"
        class="{{ $iconClasses }} absolute"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        style="display: none;"
    >
        <path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"/>
    </svg>
</button>