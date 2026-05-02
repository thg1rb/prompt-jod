@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-foreground mb-1']) }}>
    {{ $value ?? $slot }}
</label>
