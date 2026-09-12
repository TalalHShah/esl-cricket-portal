@extends('layouts.app')

@section('title', 'Match Details')

@section('content')
    @php
        $homeName = $match->homeTeam?->name ?? 'TBD';
        $awayName = $match->awayTeam?->name ?? 'TBD';
    @endphp

    <div class="mb-8 rounded-xl border border-slate-800 bg-slate-900/60 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                @include('partials.status-badge', ['status' => $match->status])
                <span class="text-sm text-slate-400">{{ optional($match->match_date)->format('d M Y, H:i') }}</span>
            </div>
            <a href="{{ route('matches.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; All matches</a>
        </div>

        <div class="mt-6 grid grid-cols-3 items-center gap-4 text-center">
            <div>
                <p class="text-lg font-bold text-white">{{ $homeName }}</p>
                <p class="text-xs uppercase text-slate-500">Home</p>
            </div>
            <div class="text-2xl font-bold text-slate-600">VS</div>
            <div>
                <p class="text-lg font-bold text-white">{{ $awayName }}</p>
                <p class="text-xs uppercase text-slate-500">Away</p>
            </div>
        </div>

        @if ($match->winnerTeam)
            <p class="mt-6 text-center text-sm text-emerald-400">
                Winner: <span class="font-semibold">{{ $match->winnerTeam->name }}</span>
            </p>
        @endif

        @if ($match->summary_notes)
            <div class="mt-6 rounded-lg border border-slate-800 bg-slate-950/60 p-4 text-sm text-slate-300">
                {{ $match->summary_notes }}
            </div>
        @endif

        <div class="mt-6 grid grid-cols-2 gap-4 border-t border-slate-800 pt-4 text-xs text-slate-500 sm:grid-cols-4">
            <div>Submitted by: <span class="text-slate-300">{{ $match->submittedBy?->name ?? '—' }}</span></div>
            <div>Confirmed by: <span class="text-slate-300">{{ $match->confirmedBy?->name ?? '—' }}</span></div>
            <div>Players: <span class="text-slate-300">{{ $stats->count() }}</span></div>
            <div>Screenshots: <span class="text-slate-300">{{ $match->screenshots->count() }}</span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @foreach ([['team' => $match->homeTeam, 'rows' => $homeStats, 'label' => $homeName], ['team' => $match->awayTeam, 'rows' => $awayStats, 'label' => $awayName]] as $side)
            <div class="rounded-xl border border-slate-800 bg-slate-900/60">
                <div class="border-b border-slate-800 px-5 py-4">
                    <h2 class="font-semibold text-white">{{ $side['label'] }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-800 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Player</th>
                                <th class="px-4 py-3 text-right">R</th>
                                <th class="px-4 py-3 text-right">B</th>
                                <th class="px-4 py-3 text-right">4s</th>
                                <th class="px-4 py-3 text-right">6s</th>
                                <th class="px-4 py-3 text-right">W</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse ($side['rows'] as $stat)
                                <tr class="hover:bg-slate-800/40">
                                    <td class="px-4 py-3">
                                        @if ($stat->player)
                                            <a href="{{ route('players.show', $stat->player) }}" class="text-white hover:text-emerald-400">
                                                {{ $stat->player->name }}
                                            </a>
                                        @else
                                            <span class="text-slate-500">Unknown</span>
                                        @endif
                                        @if ($stat->was_exceptional)
                                            <span class="ml-1 text-xs text-amber-400" title="Exceptional performance">★</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-emerald-400">{{ $stat->runs_scored }}</td>
                                    <td class="px-4 py-3 text-right text-slate-300">{{ $stat->balls_faced }}</td>
                                    <td class="px-4 py-3 text-right text-slate-300">{{ $stat->fours }}</td>
                                    <td class="px-4 py-3 text-right text-slate-300">{{ $stat->sixes }}</td>
                                    <td class="px-4 py-3 text-right text-slate-300">{{ $stat->wickets_taken }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No statistics recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection
