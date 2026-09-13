@extends('layouts.admin')

@section('title', 'Competitions')

@section('content')
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div>
            <p class="eyebrow gold mb-2">Tournaments</p>
            <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Competitions</h1>
            <p class="text-sm mt-2" style="color: var(--paper-faint);">Leagues (round-robin + playoffs) and Cups (standalone fixtures) — each with its own fixtures and points table</p>
        </div>
        <a href="{{ route('admin.competitions.create') }}" class="btn-accent px-5 py-3 whitespace-nowrap">+ New Competition</a>
    </div>

    <div class="card-section overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Teams</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($competitions as $competition)
                    <tr>
                        <td style="color: var(--paper);">
                            {{ $competition->name }}
                            @if ($competition->is_official) <span class="tag gold" style="font-size:0.6rem;">Official</span> @endif
                        </td>
                        <td style="color: var(--paper-dim);">{{ ucfirst($competition->type) }}</td>
                        <td style="color: var(--paper-dim);">{{ $competition->teams_count }}</td>
                        <td>
                            <span class="status-pill {{ $competition->status === 'completed' ? 'confirmed' : 'pending' }}">
                                {{ strtoupper(str_replace('_', ' ', $competition->status)) }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('admin.competitions.show', $competition) }}" class="btn-ghost px-3 py-1.5 text-xs">Manage</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No competitions created yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
