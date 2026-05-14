@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 bg-background border border-border rounded-xl text-foreground placeholder-text-muted focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors']) }}>