@extends('layouts.app')

@section('title', 'League Valuations')

@section('content')
    @include('partials.page-header', [
        'title' => 'League Valuations',
        'eyebrow' => 'Market',
        'subtitle' => 'Current market values across all active players',
    ])

    <div class="flex items-center justify-between flex-wrap gap-4 mb-10">
        <div class="flex gap-3">
            <a href="{{ route('valuations.league') }}" class="btn-accent px-5 py-2.5">League View</a>
            <a href="{{ route('valuations.tiers') }}" class="btn-ghost px-5 py-2.5">By Tier</a>
        </div>
        @include('partials.sort-bar', ['current' => $sort, 'sortId' => 'valuations', 'options' => [
            'value_desc' => 'Value — High to Low',
            'value_asc' => 'Value — Low to High',
            'name_asc' => 'Name — A to Z',
            'change_desc' => 'Change — Biggest Gain',
            'change_asc' => 'Change — Biggest Loss',
            'tier' => 'Tier',
        ]])
    </div>

    <div class="card-section overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player</th>
                    <th>Team</th>
                    <th>Tier</th>
                    <th style="text-align:right;">Base Value</th>
                    <th style="text-align:right;">Current Value</th>
                    <th style="text-align:right;">Change</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($players as $index => $player)
                    <tr>
                        <td style="font-weight: 600; color: var(--paper);">{{ $loop->iteration + ($players->currentPage() - 1) * $players->perPage() }}</td>
                        <td>
                            <a href="{{ route('players.show', $player) }}" class="text-link font-semibold">{{ $player->name }}</a>
                            <p class="text-xs mt-1" style="color: var(--paper-faint);">{{ $player->typeLabel() }}</p>
                        </td>
                        <td style="color: var(--paper-dim);">
                            @if ($player->team)
                                <a href="{{ route('teams.show', $player->team) }}" style="color: var(--paper-dim);" class="underline-hover">{{ $player->team->name }}</a>
                            @else
                                <span class="tag gold">Free Agent</span>
                            @endif
                        </td>
                        <td><span class="tag">{{ $player->tier }}</span></td>
                        <td style="text-align:right; color: var(--paper-dim);"><x-money :amount="$player->base_value" /></td>
                        <td style="text-align:right; font-weight: 600; color: var(--gold);"><x-money :amount="$player->current_value" /></td>
                        <td style="text-align:right; font-weight: 600;">
                            @php
                                $change = $player->current_value - $player->base_value;
                                $changePercent = $player->base_value > 0 ? ($change / $player->base_value) * 100 : 0;
                            @endphp
                            @if ($change > 0)
                                <span style="color: var(--up);">+<x-money :amount="$change" /> (+{{ number_format($changePercent, 1) }}%)</span>
                            @elseif ($change < 0)
                                <span style="color: var(--live);">-<x-money :amount="abs($change)" /> ({{ number_format($changePercent, 1) }}%)</span>
                            @else
                                <span style="color: var(--paper-faint);">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No players found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-10">
        {{ $players->links() }}
    </div>
@endsection
