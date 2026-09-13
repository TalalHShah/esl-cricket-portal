@extends('layouts.app')

@section('title', 'Competitions')

@section('content')
    @include('partials.page-header', [
        'title' => 'Competitions',
        'eyebrow' => 'ESL Tournaments',
        'subtitle' => 'Official leagues and cups run by the E-Sports League',
    ])

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        @forelse ($competitions as $competition)
            <a href="{{ route('competitions.show', $competition) }}" class="card-section lift-on-hover p-6 block" style="text-decoration:none;">
                <div class="flex items-center justify-between mb-3">
                    <span class="tag gold">{{ ucfirst($competition->type) }}</span>
                    <span class="status-pill {{ $competition->status === 'completed' ? 'confirmed' : 'pending' }}">{{ strtoupper(str_replace('_', ' ', $competition->status)) }}</span>
                </div>
                <h2 class="font-display text-2xl font-semibold mb-2" style="color: var(--paper);">{{ $competition->name }}</h2>
                @if ($competition->description)
                    <p class="text-sm mb-3" style="color: var(--paper-faint);">{{ \Illuminate\Support\Str::limit($competition->description, 100) }}</p>
                @endif
                <p class="text-xs" style="color: var(--paper-dim);">{{ $competition->teams_count }} team{{ $competition->teams_count !== 1 ? 's' : '' }}</p>
            </a>
        @empty
            <div class="sm:col-span-2">
                <div class="card-section p-12 text-center" style="color: var(--paper-faint);">No competitions announced yet — check back soon.</div>
            </div>
        @endforelse
    </div>
@endsection
