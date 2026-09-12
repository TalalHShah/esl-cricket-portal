@props(['status'])

@php
    $styles = [
        'confirmed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
        'pending_review' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
        'pending_confirmation' => 'bg-sky-500/10 text-sky-400 border-sky-500/30',
        'disputed' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
        'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
        'approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
        'rejected' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
        'cancelled' => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
        'draft' => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
        'published' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
        'archived' => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
        'scheduled' => 'bg-sky-500/10 text-sky-400 border-sky-500/30',
        'live' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
        'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
    ];
    $class = $styles[$status] ?? 'bg-slate-500/10 text-slate-400 border-slate-500/30';
    $label = ucwords(str_replace('_', ' ', (string) $status));
@endphp

<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $class }}">
    {{ $label }}
</span>
