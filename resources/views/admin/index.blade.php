@extends('layouts.app')

@section('title', 'Admin')

@section('content')
    @include('partials.page-header', [
        'title' => 'Admin Dashboard',
        'subtitle' => 'League-wide statistics, moderation queue, and settings.',
    ])

    {{-- Key stats --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        @include('partials.stat-card', ['label' => 'Users', 'value' => $stats['users'], 'hint' => $stats['admins'] . ' admins', 'accent' => 'violet'])
        @include('partials.stat-card', ['label' => 'Teams', 'value' => $stats['teams'], 'accent' => 'emerald'])
        @include('partials.stat-card', ['label' => 'Players', 'value' => $stats['players'], 'accent' => 'sky'])
        @include('partials.stat-card', ['label' => 'Matches', 'value' => $stats['matches'], 'accent' => 'amber'])
        @include('partials.stat-card', ['label' => 'Transfers', 'value' => $stats['transfers'], 'accent' => 'rose'])
        @include('partials.stat-card', ['label' => 'News Drafts', 'value' => $stats['news_drafts'], 'accent' => 'violet'])
    </div>

    {{-- Moderation + financials --}}
    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Pending Matches', 'value' => $stats['pending_matches'], 'accent' => 'amber'])
        @include('partials.stat-card', ['label' => 'Disputed Matches', 'value' => $stats['disputed_matches'], 'accent' => 'rose'])
        @include('partials.stat-card', ['label' => 'Pending Transfers', 'value' => $stats['pending_transfers'], 'accent' => 'amber'])
        @include('partials.stat-card', ['label' => 'Total Squad Value', 'value' => (float) $stats['total_squad_value'], 'accent' => 'emerald', 'money' => true])
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Finance --}}
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-6">
            <h2 class="font-semibold text-white">League Finances</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Total Budget</dt><dd class="font-semibold text-emerald-400"><x-money :amount="$stats['total_budget']" :size="14" /></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Total Spent</dt><dd class="font-semibold text-amber-400"><x-money :amount="$stats['total_spent']" :size="14" /></dd></div>
                <div class="flex justify-between border-t border-slate-800 pt-3"><dt class="text-slate-400">Remaining</dt><dd class="font-semibold text-sky-400"><x-money :amount="$stats['total_budget'] - $stats['total_spent']" :size="14" /></dd></div>
            </dl>
        </div>

        {{-- Pending transfers --}}
        <div class="rounded-xl border border-slate-800 bg-slate-900/60">
            <div class="border-b border-slate-800 px-5 py-4">
                <h2 class="font-semibold text-white">Pending Transfers</h2>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse ($pendingTransfers as $transfer)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $transfer->player?->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $transfer->fromTeam?->name ?? 'Free Agent' }} &rarr; {{ $transfer->toTeam?->name ?? 'Released' }}
                            </p>
                        </div>
                        @include('partials.status-badge', ['status' => $transfer->status])
                    </div>
                @empty
                    @include('partials.empty-state', ['message' => 'No pending transfers.'])
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent matches --}}
    <div class="mt-6 rounded-xl border border-slate-800 bg-slate-900/60">
        <div class="border-b border-slate-800 px-5 py-4">
            <h2 class="font-semibold text-white">Recently Submitted Matches</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Fixture</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($recentMatches as $match)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-5 py-3">
                                <a href="{{ route('matches.show', $match) }}" class="text-white hover:text-emerald-400">
                                    {{ $match->homeTeam?->name ?? 'TBD' }} v {{ $match->awayTeam?->name ?? 'TBD' }}
                                </a>
                            </td>
                            <td class="px-5 py-3 text-slate-400">{{ optional($match->match_date)->format('d M Y') }}</td>
                            <td class="px-5 py-3">@include('partials.status-badge', ['status' => $match->status])</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-slate-500">No matches submitted.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Settings --}}
    <div class="mt-6 rounded-xl border border-slate-800 bg-slate-900/60">
        <div class="border-b border-slate-800 px-5 py-4">
            <h2 class="font-semibold text-white">Settings</h2>
        </div>
        @forelse ($settings as $group => $groupSettings)
            <div class="border-b border-slate-800 px-5 py-4 last:border-0">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-emerald-400">{{ $group }}</h3>
                <dl class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($groupSettings as $setting)
                        <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                            <dt class="font-mono text-xs text-slate-500">{{ $setting->key }}</dt>
                            <dd class="mt-1 truncate text-sm text-white" title="{{ $setting->value }}">{{ $setting->value ?? '—' }}</dd>
                            <dd class="mt-1 text-xs text-slate-600">{{ $setting->type }}@if ($setting->is_public) &middot; public @endif</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @empty
            @include('partials.empty-state', ['message' => 'No settings configured yet.'])
        @endforelse
    </div>
@endsection
