@props(['amount', 'size' => 14])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }}>
    <x-coin :size="$size" />
    <span>{{ number_format((float) $amount, 0) }}</span>
</span>
