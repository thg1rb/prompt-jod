@props(['size' => 'default'])

@php
$sizeClasses = match($size) {
    'sm' => 'h-8 w-8',
    'default' => 'h-9 w-9',
    'lg' => 'h-10 w-10',
    default => 'h-9 w-9'
};
$iconClasses = match($size) {
    'sm' => 'h-4 w-4',
    'default' => 'h-5 w-5',
    'lg' => 'h-6 w-6',
    default => 'h-5 w-5'
};
@endphp

<button
    type="button"
    @click="$store.theme.toggle()"
    class="{{ $sizeClasses }} inline-flex items-center justify-center rounded-lg border border-border bg-card text-text-secondary hover:bg-sidebar-accent hover:text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
    aria-label="เปลี่ยนธีม"
>
    <!-- Sun icon (shown in dark mode) -->
    <svg x-show="$store.theme.isDark" {{ $iconClasses }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-transition.opacity.duration.200ms style="display: none;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>

    <!-- Moon icon (shown in light mode) -->
    <svg x-show="!$store.theme.isDark" {{ $iconClasses }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-transition.opacity.duration.200ms style="display: none;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
</button>
