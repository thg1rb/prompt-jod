@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'p-3 mb-4 text-sm text-success bg-success/10 border border-success/20 rounded-lg']) }}>
        {{ $status }}
    </div>
@endif
