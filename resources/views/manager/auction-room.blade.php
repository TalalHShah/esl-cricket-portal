@extends('layouts.auction-room')

@section('title', $auctionSession->player?->name ?? $auctionSession->name)

@section('head')
    @if(in_array($auctionSession->status, ['scheduled', 'paused'], true))
        <meta http-equiv="refresh" content="5">
    @endif
    <style>
        @keyframes bidFlash {
            0% { background-color: rgba(232, 178, 61, 0.35); }
            100% { background-color: transparent; }
        }
        @keyframes leaderPop {
            0% { transform: scale(0.85); opacity: 0.4; }
            60% { transform: scale(1.06); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes seatPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(63, 174, 114, 0.55); }
            50% { box-shadow: 0 0 0 5px rgba(63, 174, 114, 0); }
        }
        .bid-flash { animation: bidFlash 900ms ease-out; }
        .leader-pop { animation: leaderPop 420ms cubic-bezier(.2,.9,.3,1.3); }
        .seat-online .seat-dot { animation: seatPulse 1.8s infinite; }
        .seat-avatar { position: relative; width: 64px; height: 64px; border-radius: 9999px; overflow: hidden; border: 2px solid var(--line-strong); background-color: var(--surface-raised); flex-shrink: 0; }
        .seat-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .seat-crest { position: absolute; bottom: -4px; right: -4px; width: 24px; height: 24px; border-radius: 9999px; border: 2px solid var(--surface); background-color: var(--surface-raised); overflow: hidden; }
        .seat-crest img { width: 100%; height: 100%; object-fit: cover; }
        .seat-dot { position: absolute; top: -2px; right: -2px; width: 14px; height: 14px; border-radius: 9999px; background-color: var(--up); border: 2px solid var(--surface); }
        .seat-away .seat-avatar { opacity: 0.35; filter: grayscale(70%); }
        .seat-away .seat-dot { background-color: var(--paper-faint); animation: none; }
        .countdown-ring { width: 84px; height: 84px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; border: 4px solid var(--line-strong); font-family: 'Barlow Condensed', sans-serif; font-weight: 700; font-size: 1.6rem; color: var(--paper); transition: border-color 200ms, color 200ms; }
        .countdown-ring.urgent { border-color: var(--live); color: var(--live); }
        .leader-crest-avatar { display: flex; align-items: center; gap: -8px; }
        .leader-crest-avatar .stack { display: flex; align-items: center; }
        .leader-crest-avatar .crest-box, .leader-crest-avatar .avatar-box { width: 56px; height: 56px; border-radius: 9999px; overflow: hidden; border: 2px solid var(--line-strong); background-color: var(--surface-raised); }
        .leader-crest-avatar .avatar-box { margin-left: -14px; border-color: var(--gold); }
        .leader-crest-avatar img { width: 100%; height: 100%; object-fit: cover; }
    </style>
@endsection

@section('content')
    @php
        $player = $auctionSession->player;
        $isLive = $auctionSession->status === 'live';
        $isScheduled = $auctionSession->status === 'scheduled';
        $isPaused = $auctionSession->status === 'paused';
        $isOver = in_array($auctionSession->status, ['completed', 'cancelled']);
        $minimumBid = $auctionSession->current_bid > 0
            ? $auctionSession->current_bid + $auctionSession->bid_increment
            : $auctionSession->starting_bid;
        $jitsiRoom = 'ESLCricketAuction-' . $auctionSession->id;
    @endphp

    {{-- Player identity block --}}
    <div class="flex items-start gap-8 mb-8">
        <div class="player-portrait" style="width: 140px; height: 180px; flex-shrink: 0;">
            @if($player?->image)
                <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
            @else
                <div class="initials">{{ $player ? strtoupper(substr($player->name, 0, 2)) : '—' }}</div>
            @endif
        </div>
        <div class="flex-1">
            <p class="eyebrow {{ $isLive ? 'live' : 'gold' }} mb-2" id="statusLabel">
                {{ $isLive ? 'Live Now' : ($isScheduled ? 'Scheduled' : ($isPaused ? 'Paused' : 'Concluded')) }}
            </p>
            <h1 class="font-display text-4xl md:text-5xl font-semibold mb-2" style="color: var(--paper);">{{ $player?->name ?? $auctionSession->name }}</h1>
            @if($player)
                <p class="text-base" style="color: var(--paper-dim);">{{ $player->typeLabel() }} &nbsp;—&nbsp; {{ $player->country }} &nbsp;—&nbsp; {{ $player->tier }}</p>
                <p class="text-sm mt-1" style="color: var(--paper-faint);">Base value <x-money :amount="$player->base_value" /></p>
            @endif
        </div>
        @if($isLive)
            <div class="text-center">
                <div class="countdown-ring" id="countdownRing">—</div>
                <p class="text-xs mt-2" style="color: var(--paper-faint);">seconds left</p>
            </div>
        @endif
    </div>

    @if($isLive || $isPaused)
        {{-- Auction floor: seats left / bid center / seats right --}}
        <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-[1fr_2.2fr_1fr]">
            <div>
                <p class="eyebrow gold mb-3">Seats — Left</p>
                <div class="flex flex-col gap-4" id="seatsLeft"></div>
            </div>

            <div class="masthead text-center">
                <p class="stat-caption mb-3">Current Bid</p>
                <div class="flex items-center justify-center mb-4" id="leaderDisplay">
                    <p class="text-sm" style="color: var(--paper-faint);">No bids yet — be the first.</p>
                </div>
                <p class="stat-figure gold" id="currentBidFigure" style="font-size: 2.5rem;"><x-money :amount="$auctionSession->current_bid ?: 0" :size="30" /></p>
                <p class="text-sm mt-2" id="leaderName" style="color: var(--paper-dim);">&nbsp;</p>

                @if($isPaused)
                    <p class="text-sm mt-6" style="color: var(--paper-faint);">The auctioneer has paused bidding. Stay seated — it will resume shortly.</p>
                @elseif(!$joined)
                    <form method="POST" action="{{ route('manager.auction.join', $auctionSession) }}" class="mt-6">
                        @csrf
                        <button type="submit" class="btn-accent px-8 py-3">Join Auction Room</button>
                    </form>
                @elseif($team)
                    <div class="mt-6 pt-6" style="border-top: var(--rule);">
                        <p class="text-xs mb-2" style="color: var(--paper-faint);">Your remaining budget — <x-money :amount="$team->remainingBudget()" /></p>
                        <div id="timeExpiredNotice" class="hidden mb-3">
                            <span class="status-pill live">Time's Up — Waiting For The Call</span>
                        </div>
                        <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                            <button type="button" id="bidMinBtn" class="btn-accent px-8 py-3 text-base">
                                Bid <span id="minBidLabel"><x-money :amount="$minimumBid" :size="16" /></span>
                            </button>
                            <div class="flex items-center gap-2">
                                <input type="number" id="customBidInput" class="field px-3 py-2 text-sm" style="width: 10rem;" placeholder="Custom amount" min="{{ (int) $minimumBid }}" step="{{ (int) $auctionSession->bid_increment }}">
                                <button type="button" id="bidCustomBtn" class="btn-ghost px-4 py-2 text-sm">Bid This</button>
                            </div>
                        </div>
                        <p class="text-xs mt-3" id="bidError" style="color: var(--live);"></p>
                    </div>
                @else
                    <p class="text-sm mt-6" style="color: var(--paper-faint);">You are not assigned to manage a team, so you cannot bid.</p>
                @endif
            </div>

            <div>
                <p class="eyebrow gold mb-3">Seats — Right</p>
                <div class="flex flex-col gap-4" id="seatsRight"></div>
            </div>
        </div>

        {{-- Video / Voice --}}
        <div class="card-section p-6 mb-8">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <p class="eyebrow gold mb-1">Talk While You Bid</p>
                    <p class="text-xs" style="color: var(--paper-faint);">Opens a free video &amp; voice room for everyone in this auction (powered by Jitsi Meet — a public third-party service, not hosted by ESL Cricket).</p>
                </div>
                <button type="button" id="toggleCallBtn" class="btn-ghost px-5 py-2.5">Start Video / Voice Call</button>
            </div>
            <div id="jitsiContainer" class="hidden mt-6" style="height: 480px; border-radius: 2px; overflow: hidden; border: 1px solid var(--line-strong);"></div>
        </div>
    @endif

    {{-- Non-floor states --}}
    @if($isOver)
        <div class="card-section p-10 text-center">
            <p class="eyebrow gold mb-3">Auction Concluded</p>
            @if($auctionSession->highestBidder)
                <p class="font-display text-2xl font-semibold" style="color: var(--paper);">
                    Sold to {{ $auctionSession->highestBidder->name }} for <x-money :amount="$auctionSession->current_bid" />
                </p>
            @else
                <p class="text-sm" style="color: var(--paper-faint);">This player went unsold.</p>
            @endif
        </div>
    @elseif($isScheduled)
        <div class="card-section p-10 text-center">
            <p class="eyebrow gold mb-3">Auction Has Not Started</p>
            <p class="text-sm mb-6" style="color: var(--paper-faint);">Join now and you'll be seated at the table the moment bidding opens.</p>
            @if(!$joined)
                <form method="POST" action="{{ route('manager.auction.join', $auctionSession) }}">
                    @csrf
                    <button type="submit" class="btn-accent px-8 py-3">Join Auction Room</button>
                </form>
            @else
                <p class="status-pill" style="display:inline-block;">You Are Seated — Waiting to Start</p>
            @endif
        </div>
    @endif

    @if($isLive || $isPaused)
        <script src="https://meet.jit.si/external_api.js"></script>
        <script>
        (function () {
            const auctionId = {{ $auctionSession->id }};
            const stateUrl = @json(route('manager.auction.state', $auctionSession));
            const bidUrl = @json(route('manager.auction.bid', $auctionSession));
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const isPausedInitial = {{ $isPaused ? 'true' : 'false' }};
            let lastBid = null;
            let lastLeaderId = 'unset';
            let deadline = @json($auctionSession->bid_deadline_at?->toIso8601String());
            let polling = true;

            function coinSvg(size) {
                return `<svg width="${size}" height="${size}" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display:inline-block; vertical-align:-2px; flex-shrink:0;">
                    <circle cx="16" cy="16" r="15" fill="var(--gold)" stroke="var(--gold-dim)" stroke-width="1.5"/>
                    <circle cx="16" cy="16" r="10.5" fill="none" stroke="var(--gold-dim)" stroke-width="1.25"/>
                    <text x="16" y="21" font-family="Georgia, serif" font-size="14" font-weight="700" text-anchor="middle" fill="var(--gold-dim)">E</text>
                </svg>`;
            }
            function fmtMoney(n, size) {
                size = size || 26;
                return `<span class="inline-flex items-center gap-1.5">${coinSvg(size)}<span>${Math.round(n).toLocaleString('en-US')}</span></span>`;
            }

            function renderSeats(participants) {
                const left = document.getElementById('seatsLeft');
                const right = document.getElementById('seatsRight');
                if (!left || !right) return;
                left.innerHTML = '';
                right.innerHTML = '';
                participants.forEach((p, i) => {
                    const seat = document.createElement('div');
                    seat.className = 'card-section p-3 flex items-center gap-3 ' + (p.is_online ? 'seat-online' : 'seat-away');
                    const avatarSrc = p.manager_avatar || null;
                    const crestSrc = p.team_logo || null;
                    seat.innerHTML = `
                        <div class="seat-avatar">
                            ${avatarSrc ? `<img src="${avatarSrc}" alt="${p.manager_name || ''}">` : `<div class="initials" style="font-size:0.85rem;">${(p.manager_name || '?').substring(0,2).toUpperCase()}</div>`}
                            <span class="seat-dot"></span>
                            ${crestSrc ? `<span class="seat-crest"><img src="${crestSrc}" alt=""></span>` : ''}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold truncate" style="color: var(--paper);">${p.manager_name || 'Manager'}${p.is_you ? ' (You)' : ''}</p>
                            <p class="text-xs truncate" style="color: var(--paper-faint);">${p.team_short_name || p.team_name || ''}</p>
                            ${p.is_leading ? '<span class="tag gold" style="font-size:0.6rem;">Leading</span>' : ''}
                            ${!p.is_online ? '<span class="text-xs" style="color: var(--paper-faint);"> — away</span>' : ''}
                        </div>
                    `;
                    (i % 2 === 0 ? left : right).appendChild(seat);
                });
                if (participants.length === 0) {
                    left.innerHTML = '<p class="text-xs" style="color: var(--paper-faint);">No one seated yet</p>';
                }
            }

            function renderLeader(leader) {
                const el = document.getElementById('leaderDisplay');
                const nameEl = document.getElementById('leaderName');
                if (!el) return;
                if (!leader) {
                    el.innerHTML = '<p class="text-sm" style="color: var(--paper-faint);">No bids yet — be the first.</p>';
                    nameEl.textContent = ' ';
                    return;
                }
                el.innerHTML = `
                    <div class="leader-crest-avatar leader-pop">
                        <div class="crest-box">${leader.team_logo ? `<img src="${leader.team_logo}" alt="">` : ''}</div>
                        <div class="avatar-box">${leader.manager_avatar ? `<img src="${leader.manager_avatar}" alt="">` : ''}</div>
                    </div>
                `;
                nameEl.textContent = 'Leading — ' + leader.team_name + (leader.manager_name ? ' (' + leader.manager_name + ')' : '');
            }

            function tickCountdown() {
                const ring = document.getElementById('countdownRing');
                if (!ring || !deadline) { if (ring) ring.textContent = '—'; return; }
                const remaining = Math.max(0, Math.round((new Date(deadline) - new Date()) / 1000));
                ring.textContent = remaining;
                ring.classList.toggle('urgent', remaining <= 10);
            }
            setInterval(tickCountdown, 1000);
            tickCountdown();

            function applyState(data) {
                document.getElementById('statusLabel').textContent =
                    data.status === 'live' ? 'Live Now' : (data.status === 'paused' ? 'Paused' : data.status);

                if (data.current_bid !== lastBid) {
                    const fig = document.getElementById('currentBidFigure');
                    if (fig) {
                        fig.innerHTML = fmtMoney(data.current_bid, 30);
                        fig.classList.remove('bid-flash');
                        void fig.offsetWidth;
                        fig.classList.add('bid-flash');
                    }
                    lastBid = data.current_bid;
                }

                if (data.leader && data.leader.team_id !== lastLeaderId) {
                    renderLeader(data.leader);
                    lastLeaderId = data.leader.team_id;
                } else if (!data.leader && lastLeaderId !== null) {
                    renderLeader(null);
                    lastLeaderId = null;
                }

                deadline = data.deadline_at;
                tickCountdown();

                const minLabel = document.getElementById('minBidLabel');
                if (minLabel) minLabel.innerHTML = fmtMoney(data.minimum_bid, 16);
                const customInput = document.getElementById('customBidInput');
                if (customInput) customInput.min = Math.ceil(data.minimum_bid);

                const expiredNotice = document.getElementById('timeExpiredNotice');
                const bidMinBtn = document.getElementById('bidMinBtn');
                const bidCustomBtn = document.getElementById('bidCustomBtn');
                if (expiredNotice) {
                    expiredNotice.classList.toggle('hidden', !data.time_expired);
                }
                if (data.you) {
                    const disable = data.time_expired || data.you.is_leading || !data.you.has_squad_space;
                    if (bidMinBtn) bidMinBtn.disabled = disable;
                    if (bidCustomBtn) bidCustomBtn.disabled = disable;
                }

                renderSeats(data.participants || []);

                if (data.status !== 'live' && data.status !== 'paused') {
                    polling = false;
                    window.location.reload();
                } else if (isPausedInitial !== (data.status === 'paused')) {
                    window.location.reload();
                }
            }

            function poll() {
                if (!polling) return;
                fetch(stateUrl, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(applyState)
                    .catch(() => {});
            }
            poll();
            setInterval(poll, 2500);

            function placeBid(amount) {
                const errorEl = document.getElementById('bidError');
                if (errorEl) errorEl.textContent = '';
                const body = new URLSearchParams();
                body.set('_token', csrf);
                if (amount) body.set('amount', amount);
                fetch(bidUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                }).then(async r => {
                    const data = await r.json();
                    if (!r.ok) {
                        if (errorEl) errorEl.textContent = data.error || 'Could not place bid.';
                        return;
                    }
                    poll();
                }).catch(() => {
                    if (errorEl) errorEl.textContent = 'Network error — try again.';
                });
            }

            const bidMinBtn = document.getElementById('bidMinBtn');
            if (bidMinBtn) bidMinBtn.addEventListener('click', () => placeBid(null));

            const bidCustomBtn = document.getElementById('bidCustomBtn');
            if (bidCustomBtn) bidCustomBtn.addEventListener('click', () => {
                const val = document.getElementById('customBidInput').value;
                if (!val || Number(val) <= 0) {
                    document.getElementById('bidError').textContent = 'Enter a valid amount.';
                    return;
                }
                placeBid(val);
            });

            // Video/voice call (Jitsi Meet public server)
            const toggleCallBtn = document.getElementById('toggleCallBtn');
            let jitsiApi = null;
            if (toggleCallBtn) {
                toggleCallBtn.addEventListener('click', () => {
                    const container = document.getElementById('jitsiContainer');
                    if (jitsiApi) {
                        jitsiApi.dispose();
                        jitsiApi = null;
                        container.classList.add('hidden');
                        container.innerHTML = '';
                        toggleCallBtn.textContent = 'Start Video / Voice Call';
                        return;
                    }
                    container.classList.remove('hidden');
                    jitsiApi = new JitsiMeetExternalAPI('meet.jit.si', {
                        roomName: 'ESLCricketAuction-' + auctionId,
                        parentNode: container,
                        width: '100%',
                        height: '100%',
                        userInfo: { displayName: @json(auth()->user()->name ?? 'Manager') },
                        configOverwrite: { prejoinPageEnabled: false },
                    });
                    toggleCallBtn.textContent = 'End Video / Voice Call';
                });
            }
        })();
        </script>
    @endif
@endsection
