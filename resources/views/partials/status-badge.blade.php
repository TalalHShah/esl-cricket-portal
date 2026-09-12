@props(['status'])

@php
    $positive = ['confirmed', 'approved', 'published', 'completed'];
    $warning = ['pending_review', 'pending_confirmation', 'pending', 'scheduled', 'draft'];
    $negative = ['disputed', 'rejected', 'live'];

    $class = in_array($status, $positive) ? 'confirmed'
        : (in_array($status, $warning) ? 'pending'
        : (in_array($status, $negative) ? 'live' : ''));

    $label = ucwords(str_replace('_', ' ', (string) $status));
@endphp

<span class="status-pill {{ $class }}">{{ $label }}</span>
