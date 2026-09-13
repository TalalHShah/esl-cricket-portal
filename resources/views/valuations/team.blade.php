@extends('layouts.app')

@section('title', $team->name . ' Valuations')

@section('content')
    @include('partials.page-header', [
        'title' => $team->name . ' — Valuations',
        'eyebrow' => 'Market',
        'subtitle' => 'How each squad player\'s market value has moved since their base price',
    ])

    <div class="flex items-center justify-between flex-wrap gap-4 mb-10">
        <div class="flex gap-3">
            <a href="{{ route('valuations.league') }}" class="btn-ghost px-5 py-2.5">League View</a>
            <a href="{{ route('valuations.tiers') }}" class="btn-ghost px-5 py-2.5">By Tier</a>
            <a href="{{ route('teams.show', $team) }}" class="btn-ghost px-5 py-2.5">&larr; {{ $team->name }}</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 mb-10 sm:grid-cols-3">
        <div class="stat">
            <p class="stat-figure"><x-money :amount="$totalBaseValue" :size="18" /></p>
            <p class="stat-caption">Total Base Value</p>
        </div>
        <div class="stat">
            <p class="stat-figure gold"><x-money :amount="$totalCurrentValue" :size="18" /></p>
            <p class="stat-caption">Total Current Value</p>
        </div>
        <div class="stat">
            <p class="stat-figure" style="color: {{ $gainLoss > 0 ? 'var(--up)' : ($gainLoss < 0 ? 'var(--live)' : 'var(--paper-dim)') }};">
                {{ $gainLoss > 0 ? '+' : '' }}<x-money :amount="$gainLoss" :size="18" />
            </p>
            <p class="stat-caption">Net Change</p>
        </div>
    </div>

    <div class="card-section overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Tier</th>
                    <th style="text-align:right;">Base Value</th>
                    <th style="text-align:right;">Current Value</th>
                    <th style="text-align:right;">Change</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($players as $player)
                    <tr>
                        <td>
                            <a href="{{ route('players.show', $player) }}" class="text-link font-semibold">{{ $player->name }}</a>
                            <p class="text-xs mt-1" style="color: var(--paper-faint);">{{ $player->typeLabel() }}</p>
                        </td>
                        <td><span class="tag {{ $player->is_manager_player ? 'gold' : '' }}">{{ $player->is_manager_player ? 'Manager' : $player->tier }}</span></td>
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
                        <td colspan="5" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No players in this squad yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
