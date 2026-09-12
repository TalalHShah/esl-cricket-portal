@extends('layouts.admin')

@section('title', 'Matches')

@section('content')
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div>
            <p class="eyebrow gold mb-2">Fixtures</p>
            <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Matches</h1>
            <p class="text-sm mt-2" style="color: var(--paper-faint);">{{ $matches->total() }} match{{ $matches->total() !== 1 ? 'es' : '' }} — confirming a result recalculates player valuations</p>
        </div>
        <div class="flex items-center gap-3">
            @include('partials.sort-bar', ['current' => $sort, 'sortId' => 'matches', 'options' => [
                'date_desc' => 'Newest First',
                'date_asc' => 'Oldest First',
                'status' => 'Status',
            ]])
            <a href="{{ route('admin.matches.create') }}" class="btn-accent px-5 py-3 whitespace-nowrap">+ Schedule Match</a>
        </div>
    </div>

    <div class="card-section overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fixture</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Winner</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($matches as $match)
                    <tr>
                        <td style="color: var(--paper);">{{ $match->homeTeam?->name }} <span style="color: var(--paper-faint);">vs</span> {{ $match->awayTeam?->name }}</td>
                        <td style="color: var(--paper-dim);">{{ $match->match_date->format('d M Y') }}</td>
                        <td>
                            <span class="status-pill {{ $match->status === 'confirmed' ? 'confirmed' : ($match->status === 'disputed' ? 'live' : 'pending') }}">
                                {{ strtoupper(str_replace('_', ' ', $match->status)) }}
                            </span>
                        </td>
                        <td style="color: var(--gold); font-weight: 600;">{{ $match->winnerTeam?->name ?? '—' }}</td>
                        <td style="text-align:right;">
                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                <a href="{{ route('admin.matches.edit', $match) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                    {{ $match->status === 'confirmed' ? 'View' : 'Edit / Record Result' }}
                                </a>
                                @if ($match->status !== 'confirmed')
                                    <form method="POST" action="{{ route('admin.matches.destroy', $match) }}" onsubmit="return confirm('Delete this match?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-ghost px-3 py-1.5 text-xs" style="color: var(--live);">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No matches scheduled yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $matches->links() }}
    </div>
@endsection
