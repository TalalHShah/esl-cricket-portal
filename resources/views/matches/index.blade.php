@extends('layouts.app')

@section('title', 'Matches')

@section('content')
    @include('partials.page-header', [
        'title' => 'Matches',
        'eyebrow' => 'Schedule',
        'subtitle' => $matches->total() . ' match' . ($matches->total() !== 1 ? 'es' : '') . ' recorded',
    ])

    <div class="card-section mb-10 p-6">
        <form method="GET" action="{{ route('matches.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="eyebrow block mb-2">Status</label>
                <select name="status" class="field px-3 py-2 text-sm">
                    <option value="">All Statuses</option>
                    @foreach (['pending_review' => 'Pending Review', 'pending_confirmation' => 'Pending Confirmation', 'confirmed' => 'Confirmed', 'disputed' => 'Disputed'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="eyebrow block mb-2">Sort</label>
                <select name="sort" class="field px-3 py-2 text-sm">
                    <option value="date_desc" @selected($sort === 'date_desc')>Date — Newest First</option>
                    <option value="date_asc" @selected($sort === 'date_asc')>Date — Oldest First</option>
                    <option value="status" @selected($sort === 'status')>Status</option>
                </select>
            </div>
            <button type="submit" class="btn-accent px-5 py-2.5">Filter</button>
            <a href="{{ route('matches.index') }}" class="btn-ghost px-5 py-2.5">Reset</a>
        </form>
    </div>

    <div class="card-section">
        @forelse ($matches as $match)
            <a href="{{ route('matches.show', $match) }}" class="fixture-row row-hover block">
                <p class="eyebrow mb-2">{{ optional($match->match_date)->format('d M Y, H:i') }}</p>
                <p class="fixture-teams mb-3">
                    {{ $match->homeTeam?->name ?? 'TBD' }}<span class="vs">vs</span>{{ $match->awayTeam?->name ?? 'TBD' }}
                </p>
                <div class="flex items-center gap-3">
                    @include('partials.status-badge', ['status' => $match->status])
                    @if($match->winnerTeam)
                        <span class="text-xs" style="color: var(--paper-faint);">Winner — {{ $match->winnerTeam->name }}</span>
                    @endif
                </div>
            </a>
        @empty
            @include('partials.empty-state', ['message' => 'No matches found.'])
        @endforelse
    </div>

    <div class="mt-10">
        {{ $matches->links() }}
    </div>
@endsection
