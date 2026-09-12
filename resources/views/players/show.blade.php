@extends('layouts.app')

@section('title', $player->name)

@section('content')
    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Profile --}}
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-6">
            <div class="flex items-center gap-4">
                <span class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-800 text-xl font-bold text-slate-300">
                    {{ strtoupper(substr($player->name, 0, 1)) }}
                </span>
                <div>
                    <h1 class="text-xl font-bold text-white">{{ $player->name }}</h1>
                    <p class="text-sm text-slate-400">{{ $player->country }}</p>
                </div>
            </div>

            <dl class="mt-6 space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Role</dt><dd class="text-slate-200">{{ $player->role }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Tier</dt><dd class="text-slate-200">{{ $player->tier }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Age</dt><dd class="text-slate-200">{{ $player->age ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Batting Style</dt><dd class="text-slate-200">{{ $player->batting_style ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Bowling Style</dt><dd class="text-slate-200">{{ $player->bowling_style ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Status</dt>
                    <dd class="text-slate-200">{{ $player->isFreeAgent() ? 'Free Agent' : 'Contracted' }}</dd>
                </div>
            </dl>

            <div class="mt-6 space-y-2 border-t border-slate-800 pt-4">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Base Value</span>
                    <span class="font-semibold text-slate-200">{{ number_format((float) $player->base_value, 0) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Current Value</span>
                    <span class="font-semibold text-emerald-400">{{ number_format((float) $player->current_value, 0) }}</span>
                </div>
                @if (! is_null($player->sold_price))
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Sold Price</span>
                        <span class="font-semibold text-sky-400">{{ number_format((float) $player->sold_price, 0) }}</span>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                @if ($player->team)
                    <a href="{{ route('teams.show', $player->team) }}"
                       class="block rounded-md border border-slate-700 px-4 py-2 text-center text-sm text-slate-200 hover:bg-slate-800">
                        {{ $player->team->name }}
                    </a>
                @else
                    <span class="block rounded-md border border-dashed border-slate-700 px-4 py-2 text-center text-sm text-slate-500">
                        No team
                    </span>
                @endif
            </div>
        </div>

        {{-- Career totals --}}
        <div class="lg:col-span-2">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                @include('partials.stat-card', ['label' => 'Matches', 'value' => $totals['matches'], 'accent' => 'violet'])
                @include('partials.stat-card', ['label' => 'Runs', 'value' => $totals['runs'], 'accent' => 'emerald'])
                @include('partials.stat-card', ['label' => 'Balls Faced', 'value' => $totals['balls'], 'accent' => 'sky'])
                @include('partials.stat-card', ['label' => 'Fours', 'value' => $totals['fours'], 'accent' => 'amber'])
                @include('partials.stat-card', ['label' => 'Sixes', 'value' => $totals['sixes'], 'accent' => 'rose'])
                @include('partials.stat-card', ['label' => 'Wickets', 'value' => $totals['wickets'], 'accent' => 'emerald'])
            </div>
        </div>
    </div>

    {{-- Match stats --}}
    <div class="rounded-xl border border-slate-800 bg-slate-900/60">
        <div class="border-b border-slate-800 px-5 py-4">
            <h2 class="font-semibold text-white">Match Statistics</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Match</th>
                        <th class="px-5 py-3 text-right">Runs</th>
                        <th class="px-5 py-3 text-right">Balls</th>
                        <th class="px-5 py-3 text-right">4s</th>
                        <th class="px-5 py-3 text-right">6s</th>
                        <th class="px-5 py-3 text-right">Wkts</th>
                        <th class="px-5 py-3 text-right">Overs</th>
                        <th class="px-5 py-3 text-right">Catches</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($stats as $stat)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-5 py-3">
                                @if ($stat->match)
                                    <a href="{{ route('matches.show', $stat->match) }}" class="text-white hover:text-emerald-400">
                                        {{ $stat->match->homeTeam?->short_name ?? $stat->match->homeTeam?->name }}
                                        v
                                        {{ $stat->match->awayTeam?->short_name ?? $stat->match->awayTeam?->name }}
                                    </a>
                                    <span class="block text-xs text-slate-500">{{ optional($stat->match->match_date)->format('d M Y') }}</span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-emerald-400">{{ $stat->runs_scored }}</td>
                            <td class="px-5 py-3 text-right text-slate-300">{{ $stat->balls_faced }}</td>
                            <td class="px-5 py-3 text-right text-slate-300">{{ $stat->fours }}</td>
                            <td class="px-5 py-3 text-right text-slate-300">{{ $stat->sixes }}</td>
                            <td class="px-5 py-3 text-right text-slate-300">{{ $stat->wickets_taken }}</td>
                            <td class="px-5 py-3 text-right text-slate-300">{{ $stat->overs_bowled }}</td>
                            <td class="px-5 py-3 text-right text-slate-300">{{ $stat->catches }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-500">No match statistics recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
