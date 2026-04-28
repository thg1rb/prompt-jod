@props(['size' => 'default'])

@php
$sizeClasses = match($size) {
    'sm' => 'h-8 w-8',
    'default' => 'h-9 w-9',
    'lg' => 'h-10 w-10',
    default => 'h-9 w-9'
};
$iconClasses = match($size) {
    'sm' => 'h-3.5 w-3.5',
    'default' => 'h-4 w-4',
    'lg' => 'h-5 w-5',
    default => 'h-4 w-4'
};
@endphp

<button
    type="button"
    @click="$store.theme.toggle()"
    class="{{ $sizeClasses }} inline-flex items-center justify-center rounded-lg border border-border bg-background text-muted-foreground hover:bg-muted hover:text-foreground transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background"
    aria-label="เปลี่ยนธีม"
>
    <!-- Sun icon (shown in dark mode) -->
    <svg x-show="$store.theme.isDark" {{ $iconClasses }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 rotate-90" x-transition:enter-end="opacity-100 rotate-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 rotate-0" x-transition:leave-end="opacity-0 rotate-90" style="display: none;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>

    <!-- Moon icon (shown in light mode) -->
    <svg x-show="!$store.theme.isDark" {{ $iconClasses }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 rotate-0" x-transition:leave-end="opacity-0 -rotate-90" style="display: none;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
</button>
