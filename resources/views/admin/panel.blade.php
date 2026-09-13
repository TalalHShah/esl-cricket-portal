@extends('layouts.admin')

@section('title', 'Admin Control Panel')

@section('content')
    @include('partials.page-header', [
        'title' => 'Admin Control Panel',
        'eyebrow' => 'League Command',
        'subtitle' => 'Managers, teams, players, the draft, auctions, and league settings — all in one place.',
    ])

    @if (session('status'))
        <div class="mb-6 rounded-lg border px-4 py-3 text-sm" style="background-color: rgba(63,174,114,0.08); border-color: var(--up); color: var(--up);">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 rounded-lg border px-4 py-3 text-sm" style="background-color: rgba(214,69,90,0.08); border-color: var(--live); color: var(--live);">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    @php
        $statusMeta = [
            'open' => ['label' => 'Open', 'color' => 'up', 'desc' => 'Bidding is live. Managers can only shortlist from Scouts.'],
            'announced' => ['label' => 'Announced', 'color' => 'gold', 'desc' => 'Announced but not started. Managers can only shortlist from Scouts.'],
            'closed' => ['label' => 'Closed', 'color' => 'live', 'desc' => 'The Transfer Window can now open on its configured schedule.'],
        ];
        $current = $statusMeta[$auctionStatus] ?? $statusMeta['closed'];
    @endphp

    {{-- Top row: Auction Status + Transfer Window, compact --}}
    <div class="card-section p-5 mb-6" style="border-top: 3px solid var(--{{ $current['color'] }});">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-8 flex-wrap">
                <div>
                    <p class="eyebrow mb-1">Auction Status</p>
                    <p class="font-display text-2xl font-semibold" style="color: var(--{{ $current['color'] }});">{{ $current['label'] }}</p>
                </div>
                <div>
                    <p class="eyebrow mb-1">Transfer Window</p>
                    <p class="font-display text-2xl font-semibold" style="color: {{ $transferWindowOpen ? 'var(--up)' : 'var(--live)' }};">{{ $transferWindowOpen ? 'Open' : 'Closed' }}</p>
                </div>
                <p class="text-xs max-w-sm" style="color: var(--paper-faint);">{{ $current['desc'] }}</p>
            </div>
            <form action="{{ route('admin.settings.auction-status.update') }}" method="POST" class="flex flex-wrap gap-2">
                @csrf
                @foreach ($statusMeta as $value => $meta)
                    <button type="submit" name="auction_status" value="{{ $value }}"
                            class="{{ $auctionStatus === $value ? 'btn-accent' : 'btn-ghost' }} px-4 py-2 text-xs">
                        {{ $meta['label'] }}
                    </button>
                @endforeach
                <a href="{{ route('admin.settings.index') }}" class="btn-ghost px-4 py-2 text-xs">Schedule &rarr;</a>
            </form>
        </div>
    </div>

    {{-- Compact stats strip --}}
    <div class="grid grid-cols-2 gap-4 mb-6 sm:grid-cols-5">
        <div class="stat"><p class="stat-figure">{{ $stats['managers'] }}</p><p class="stat-caption">Managers</p></div>
        <div class="stat"><p class="stat-figure">{{ $stats['admins'] }}</p><p class="stat-caption">Admins</p></div>
        <div class="stat"><p class="stat-figure">{{ $stats['teams'] }}</p><p class="stat-caption">Teams</p></div>
        <div class="stat"><p class="stat-figure">{{ $stats['players'] }}</p><p class="stat-caption">Players</p></div>
        <div class="stat"><p class="stat-figure gold"><x-money :amount="$stats['total_budget']" :size="18" /></p><p class="stat-caption">Total Budget</p></div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        {{-- Draft Control --}}
        <div class="card-section overflow-hidden">
            <div class="card-header">
                <h2>Draft Control</h2>
                @if ($draft)
                    <span class="tag {{ in_array($draft->status, ['active', 'bonus_round']) ? 'gold' : '' }}">{{ str_replace('_', ' ', $draft->status) }}</span>
                @endif
            </div>
            <div class="p-6 space-y-4">
                @if (! $draft)
                    <p class="text-sm" style="color: var(--paper-faint);">No draft has been run yet.</p>
                @else
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="eyebrow mb-1">Current Country</p>
                            <p style="color: var(--paper);">{{ $draft->current_country ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="eyebrow mb-1">Queued For Auction</p>
                            <p style="color: var(--paper);">{{ $draftPoolCount }} player(s)</p>
                        </div>
                    </div>
                @endif

                <div class="flex flex-wrap gap-2 pt-2" style="border-top: var(--rule);">
                    <a href="{{ route('manager.draft') }}" class="btn-ghost px-4 py-2 text-xs">Open Draft Room</a>

                    @if ($draft && in_array($draft->status, ['active', 'bonus_round'], true))
                        <form action="{{ route('admin.draft.end', $draft) }}" method="POST" onsubmit="return confirm('End the draft now? Anything already queued stays available for the Auction phase.');">
                            @csrf
                            <button type="submit" class="btn-ghost px-4 py-2 text-xs" style="color: var(--live);">End Draft</button>
                        </form>
                        <form action="{{ route('admin.draft.cancel', $draft) }}" method="POST" onsubmit="return confirm('Cancel this draft entirely? This cannot be undone.');">
                            @csrf
                            <button type="submit" class="btn-ghost px-4 py-2 text-xs" style="color: var(--live);">Cancel Draft</button>
                        </form>
                    @endif

                    @if ($draft && $draft->status === 'completed' && $anyPlayersRemainForDraft)
                        <form action="{{ route('admin.draft.bonus-round', $draft) }}" method="POST" onsubmit="return confirm('Open the Bonus Round? Any manager will be able to pick freely, country by country.');">
                            @csrf
                            <button type="submit" class="btn-accent px-4 py-2 text-xs">Start Bonus Round</button>
                        </form>
                    @endif

                    @if (! $draft || in_array($draft->status, ['completed'], true))
                        <a href="{{ route('admin.draft.create') }}" class="btn-accent px-4 py-2 text-xs">
                            {{ $draft ? 'Start Another Draft' : 'Start Country Draft' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Auction Control --}}
        <div class="card-section overflow-hidden">
            <div class="card-header">
                <h2>Auction Control</h2>
                @if ($liveAuction)
                    <span class="tag gold">{{ $liveAuction->status }}</span>
                @endif
            </div>
            <div class="p-6 space-y-4">
                @if ($liveAuction)
                    <div class="text-sm">
                        <p class="eyebrow mb-1">On The Block</p>
                        <p style="color: var(--paper);">{{ $liveAuction->player?->name }} — current bid <x-money :amount="$liveAuction->current_bid" :size="14" /> ({{ $liveAuction->highestBidder?->name ?? 'no bidder yet' }})</p>
                    </div>
                @else
                    <p class="text-sm" style="color: var(--paper-faint);">No auction is currently live.</p>
                @endif
                <p class="text-sm" style="color: var(--paper-dim);">{{ $scheduledAuctionCount }} lot(s) scheduled and waiting to be started.</p>

                <div class="flex flex-wrap gap-2 pt-2" style="border-top: var(--rule);">
                    <a href="{{ route('admin.auctions.index') }}" class="btn-ghost px-4 py-2 text-xs">Manage Auctions</a>
                    <a href="{{ route('admin.auctions.create') }}" class="btn-accent px-4 py-2 text-xs">Queue Player For Auction</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Team Roster Control --}}
    <div class="card-section overflow-hidden mb-6">
        <div class="card-header"><h2>Team Roster Control</h2></div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Team</th>
                        <th style="text-align:right;">Squad</th>
                        <th style="text-align:right;">Remaining Budget</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teams as $team)
                        <tr>
                            <td style="color: var(--paper);">{{ $team->name }}</td>
                            <td style="text-align:right; color: var(--paper-dim);">{{ $team->players_count }} / {{ \App\Models\Team::SQUAD_LIMIT }}</td>
                            <td style="text-align:right; color: var(--gold); font-weight: 600;"><x-money :amount="$team->remainingBudget()" /></td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.teams.roster', $team) }}" class="btn-ghost px-3 py-1.5 text-xs">Manage Roster</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Compact management grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-6">
        @foreach ([
            ['label' => 'Managers', 'index' => 'admin.managers.index', 'create' => 'admin.managers.create', 'createLabel' => '+ Add Manager'],
            ['label' => 'Teams', 'index' => 'admin.teams.index', 'create' => 'admin.teams.create', 'createLabel' => '+ Add Team'],
            ['label' => 'Players', 'index' => 'admin.players.index', 'create' => 'admin.players.create', 'createLabel' => '+ Add Player'],
            ['label' => 'Matches', 'index' => 'admin.matches.index', 'create' => 'admin.matches.create', 'createLabel' => '+ Schedule Match'],
            ['label' => 'Competitions', 'index' => 'admin.competitions.index', 'create' => 'admin.competitions.create', 'createLabel' => '+ New Competition'],
            ['label' => 'News', 'index' => 'admin.news.index', 'create' => 'admin.news.create', 'createLabel' => '+ New Article'],
        ] as $section)
            <div class="card-section p-5 flex items-center justify-between gap-4">
                <p class="font-display text-lg font-semibold" style="color: var(--paper);">{{ $section['label'] }}</p>
                <div class="flex gap-2">
                    <a href="{{ route($section['index']) }}" class="btn-ghost px-3 py-2 text-xs whitespace-nowrap">View</a>
                    <a href="{{ route($section['create']) }}" class="btn-accent px-3 py-2 text-xs whitespace-nowrap">{{ $section['createLabel'] }}</a>
                </div>
            </div>
        @endforeach

        <div class="card-section p-5 flex items-center justify-between gap-4">
            <p class="font-display text-lg font-semibold" style="color: var(--paper);">Settings</p>
            <a href="{{ route('admin.settings.index') }}" class="btn-accent px-3 py-2 text-xs whitespace-nowrap">League Settings</a>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="card-section p-5">
        <p class="eyebrow gold mb-3">Quick Links</p>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <a href="{{ route('dashboard') }}" class="btn-ghost px-3 py-2 text-xs text-center">League Dashboard</a>
            <a href="{{ route('teams.index') }}" class="btn-ghost px-3 py-2 text-xs text-center">Public Teams</a>
            <a href="{{ route('players.index') }}" class="btn-ghost px-3 py-2 text-xs text-center">Public Players</a>
            <a href="{{ route('managers.index') }}" class="btn-ghost px-3 py-2 text-xs text-center">Public Managers</a>
        </div>
    </div>
@endsection
