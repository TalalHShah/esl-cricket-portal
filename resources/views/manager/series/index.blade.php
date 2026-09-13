@extends('layouts.manager')

@section('title', 'Series')

@section('content')
    <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow gold mb-2">Bilateral &amp; Tri-Series</p>
            <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Series</h1>
            <p class="text-sm mt-2" style="color: var(--paper-faint);">Set up a series or tri-series against other teams — separate from the official league.</p>
        </div>
        <a href="{{ route('manager.series.create') }}" class="btn-accent px-5 py-2.5">+ New Series</a>
    </div>

    @if (session('status'))
        <div class="card-section p-4 mb-8" style="border-left: 2px solid var(--up); color: var(--up);">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="card-section p-4 mb-8" style="border-left: 2px solid var(--live); color: var(--live);">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    @if (! $team)
        <div class="card-section p-8 text-center" style="color: var(--paper-faint);">You are not assigned to manage a team.</div>
    @else
        <div class="card-section overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Series</th>
                        <th>Teams</th>
                        <th>Status</th>
                        <th style="text-align:right;">Fixtures</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($series as $s)
                        @php
                            $myInvite = $s->teams->firstWhere('id', $team->id);
                            $needsResponse = $myInvite && $myInvite->pivot->status === 'invited';
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('manager.series.show', $s) }}" class="text-link font-semibold">{{ $s->name }}</a>
                                @if ($needsResponse)
                                    <span class="tag gold" style="margin-left: 0.5rem;">Response Needed</span>
                                @endif
                                <p class="text-xs mt-1" style="color: var(--paper-faint);">Hosted by {{ $s->createdByTeam?->name }}{{ $s->venue ? ' — '.$s->venue : '' }}</p>
                            </td>
                            <td style="color: var(--paper-dim);">{{ $s->teams->pluck('name')->join(' vs ') }}</td>
                            <td><span class="tag {{ $s->status === 'active' ? 'gold' : '' }}">{{ str_replace('_', ' ', $s->status) }}</span></td>
                            <td style="text-align:right; color: var(--paper-dim);">{{ $s->matches()->count() }}</td>
                            <td style="text-align:right;"><a href="{{ route('manager.series.show', $s) }}" class="btn-ghost px-3 py-1.5 text-xs">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No series yet — create one to challenge another team.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection
