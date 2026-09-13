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
        .tag.passed { border-color: var(--paper-faint); color: var(--paper-faint); }

        /* ---------- Split-room layout: bid stage + sidebar, side by side ---------- */
        .draft-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; align-items: start; }
        @media (min-width: 1024px) {
            .draft-grid { grid-template-columns: minmax(0, 1fr) 320px; }
            .draft-sidebar { position: sticky; top: 1.5rem; }
        }
        .sidebar-tabs { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
        .sidebar-tab { flex: 1; padding: 0.55rem 0.5rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; border: 1px solid var(--line); background: transparent; color: var(--paper-faint); cursor: pointer; border-radius: 3px; transition: color 150ms, border-color 150ms; }
        .sidebar-tab.is-active { border-color: var(--gold); color: var(--gold); }
        .sidebar-panel.hidden { display: none; }

        @keyframes overlayFadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes overlayCardIn { 0% { transform: scale(0.7) translateY(40px); opacity: 0; } 70% { transform: scale(1.03) translateY(-6px); opacity: 1; } 100% { transform: scale(1) translateY(0); opacity: 1; } }
        @keyframes overlayLogoSpin { 0% { transform: scale(1.4) rotate(-8deg); opacity: 0; } 100% { transform: scale(1) rotate(0deg); opacity: 0.18; } }
        #soldOverlay { position: fixed; inset: 0; z-index: 999; display: none; align-items: center; justify-content: center; animation: overlayFadeIn 400ms ease-out; }
        #soldOverlay.is-visible { display: flex; }
        #soldOverlay .overlay-bg { position: absolute; inset: 0; }
        #soldOverlay .overlay-logo-watermark { position: absolute; top: 50%; left: 50%; width: 70vh; height: 70vh; transform: translate(-50%, -50%); animation: overlayLogoSpin 900ms ease-out forwards; }
        #soldOverlay .overlay-logo-watermark img { width: 100%; height: 100%; object-fit: contain; filter: brightness(0) invert(1); }
        #soldOverlay .overlay-card { position: relative; text-align: center; padding: 3rem 3.5rem; animation: overlayCardIn 650ms cubic-bezier(.2,.9,.25,1.2); max-width: 90vw; }
        #soldOverlay .overlay-avatars { display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; }
        #soldOverlay .overlay-crest, #soldOverlay .overlay-avatar { width: 96px; height: 96px; border-radius: 9999px; overflow: hidden; border: 3px solid rgba(255,255,255,0.85); background-color: rgba(0,0,0,0.25); box-shadow: 0 8px 30px rgba(0,0,0,0.4); }
        #soldOverlay .overlay-avatar { margin-left: -24px; }
        #soldOverlay .overlay-crest img, #soldOverlay .overlay-avatar img { width: 100%; height: 100%; object-fit: cover; }
        #soldOverlay .overlay-sold-tag { font-family: 'Barlow Condensed', sans-serif; font-weight: 700; letter-spacing: 0.3em; text-transform: uppercase; font-size: 1rem; color: rgba(255,255,255,0.85); margin-bottom: 0.75rem; }
        #soldOverlay .overlay-player { font-family: 'Barlow Condensed', sans-serif; font-weight: 600; font-size: clamp(2rem, 5vw, 3.25rem); color: #fff; margin-bottom: 0.5rem; line-height: 1.05; }
        #soldOverlay .overlay-price { font-size: clamp(1.5rem, 3vw, 2rem); color: #fff; font-weight: 700; margin-bottom: 1rem; }
        #soldOverlay .overlay-team { font-size: 1.15rem; color: rgba(255,255,255,0.9); margin-bottom: 0.25rem; }
        #soldOverlay .overlay-manager { font-size: 0.95rem; color: rgba(255,255,255,0.7); margin-bottom: 2rem; }
        #soldOverlay .overlay-continue { background: rgba(255,255,255,0.95); color: #111; border: none; padding: 0.75rem 2rem; border-radius: 2px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.85rem; cursor: pointer; }
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
            <h1 class="font-display text-4xl md:text-5xl font-semibold mb-2" style="color: var(--paper);">{{ $player?->name ?? $auctionSession->name }} <span id="shortlistBadge"></span></h1>
            @if($player)
                <p class="text-base" style="color: var(--paper-dim);">{{ $player->typeLabel() }} &nbsp;—&nbsp; {{ $player->country }} &nbsp;—&nbsp; {{ $player->tier }}</p>
                <p class="text-sm mt-1" style="color: var(--paper-faint);">Base value <x-money :amount="$player->base_value" /></p>
            @endif
        </div>
        @if($isLive)
            <div class="text-center">
                <div class="countdown-ring" id="countdownRing">—</div>
                <p class="text-xs mt-2" style="color: var(--paper-faint);">time left</p>
            </div>
        @endif
    </div>

    @if($isLive || $isPaused)
        {{-- Bid stage + sidebar (seated managers / live call), side by side --}}
        <div class="draft-grid mb-8">
            <div class="draft-stage">
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
                                <button type="button" id="passBtn" class="btn-ghost px-6 py-3 text-base" style="color: var(--live); border-color: var(--live);">Pass</button>
                            </div>
                            <p class="text-xs mt-2" id="passedNotice" style="color: var(--paper-faint); display:none;">You've passed on this bid — you'll get another chance if someone bids higher.</p>
                            <p class="text-xs mt-3" id="bidError" style="color: var(--live);"></p>
                        </div>
                    @else
                        <p class="text-sm mt-6" style="color: var(--paper-faint);">You are not assigned to manage a team, so you cannot bid.</p>
                    @endif
                </div>
            </div>

            <aside class="draft-sidebar">
                <div class="sidebar-tabs">
                    <button type="button" class="sidebar-tab is-active" data-sidebar-tab="seats">Seated Managers</button>
                    <button type="button" class="sidebar-tab" data-sidebar-tab="shortlist">Shortlist</button>
                    @if($auctionSession->source === 'market')
                        <button type="button" class="sidebar-tab" data-sidebar-tab="share">Share</button>
                    @endif
                    <button type="button" class="sidebar-tab" data-sidebar-tab="call">Live Call</button>
                </div>

                <div class="sidebar-panel" id="sidebarSeats">
                    <p class="eyebrow mb-2" style="color: var(--paper-faint);">At The Table</p>
                    <div class="flex flex-col gap-3" id="seatsList"></div>
                </div>

                <div class="sidebar-panel hidden" id="sidebarShortlist">
                    <p class="eyebrow mb-2" style="color: var(--paper-faint);">Your Shortlist</p>
                    <p class="text-xs mb-3" style="color: var(--paper-faint);">Players you've starred from <a href="{{ route('manager.scouts') }}" class="underline">Scouts</a> — today's lot is highlighted if it's one of them.</p>
                    <div id="shortlistList" class="flex flex-col gap-2"></div>
                </div>

                @if($auctionSession->source === 'market')
                    @php
                        $shareMessage = "🏏 Free agent auction open: {$player?->name}\n"
                            . "Starting bid: PKR " . number_format($auctionSession->starting_bid, 0) . "\n"
                            . "Closes: " . $auctionSession->bid_deadline_at?->format('D, M j g:i A') . " (1 hour from opening)\n"
                            . "Bid here: " . route('manager.auction.room', $auctionSession);
                    @endphp
                    <div class="sidebar-panel hidden" id="sidebarShare">
                        <p class="eyebrow mb-2" style="color: var(--paper-faint);">Notify The League</p>
                        <p class="text-xs mb-3" style="color: var(--paper-faint);">This auction won't send itself — let the WhatsApp group know it's live so everyone gets a fair chance to bid.</p>
                        <textarea readonly id="shareMessageText" class="field px-3 py-2 text-xs w-full mb-3" rows="6" style="resize:none;">{{ $shareMessage }}</textarea>
                        <div class="flex flex-col gap-2">
                            <a href="https://wa.me/?text={{ rawurlencode($shareMessage) }}" target="_blank" rel="noopener" class="btn-accent w-full py-2.5 text-xs text-center">Open In WhatsApp</a>
                            <button type="button" id="copyShareBtn" class="btn-ghost w-full py-2.5 text-xs">Copy Message</button>
                        </div>
                    </div>
                @endif

                <div class="sidebar-panel hidden" id="sidebarCall">
                    <div class="card-section p-4">
                        <p class="eyebrow gold mb-1">Talk While You Bid</p>
                        <p class="text-xs mb-4" style="color: var(--paper-faint);">Opens a free video &amp; voice call in its own window (powered by Jitsi Meet — a public third-party service, not hosted by ESL Cricket). It keeps running even if you navigate around the rest of the site in this tab.</p>
                        <button type="button" id="callBtn" class="btn-accent px-5 py-2.5 w-full">Start Video / Voice Call</button>
                    </div>
                </div>
            </aside>
        </div>
    @endif

    <div id="soldOverlay" class="hidden"></div>

    @if($isOver || $isScheduled)
        <script>
            if (window.setLiveStatus) {
                window.setLiveStatus(@json($isOver ? 'This lot has concluded.' : ($joined ? "You're seated — waiting for the auctioneer to start." : 'Join now to be seated before bidding opens.')), false);
            }
        </script>
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
        <script>
        (function () {
            const auctionId = {{ $auctionSession->id }};
            const isDraftLot = {{ $auctionSession->auction_draft_id ? 'true' : 'false' }};
            const draftRoomUrl = @json(route('manager.draft'));
            window.continueAfterAuction = function () {
                if (isDraftLot) {
                    window.location.href = draftRoomUrl;
                } else {
                    window.location.reload();
                }
            };
            const stateUrl = @json(route('manager.auction.state', $auctionSession));
            const bidUrl = @json(route('manager.auction.bid', $auctionSession));
            const passUrl = @json(route('manager.auction.pass', $auctionSession));
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

            // ---------- Sidebar tabs (Seated Managers / Shortlist / Share / Live Call) ----------
            document.querySelectorAll('.sidebar-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('is-active'));
                    tab.classList.add('is-active');
                    document.getElementById('sidebarSeats').classList.toggle('hidden', tab.dataset.sidebarTab !== 'seats');
                    document.getElementById('sidebarShortlist').classList.toggle('hidden', tab.dataset.sidebarTab !== 'shortlist');
                    const shareEl = document.getElementById('sidebarShare');
                    if (shareEl) shareEl.classList.toggle('hidden', tab.dataset.sidebarTab !== 'share');
                    document.getElementById('sidebarCall').classList.toggle('hidden', tab.dataset.sidebarTab !== 'call');
                });
            });

            const copyShareBtn = document.getElementById('copyShareBtn');
            if (copyShareBtn) {
                copyShareBtn.addEventListener('click', () => {
                    const text = document.getElementById('shareMessageText').value;
                    navigator.clipboard.writeText(text).then(() => {
                        copyShareBtn.textContent = 'Copied!';
                        setTimeout(() => { copyShareBtn.textContent = 'Copy Message'; }, 1500);
                    }).catch(() => {
                        const el = document.getElementById('shareMessageText');
                        el.select();
                        document.execCommand('copy');
                    });
                });
            }

            function renderShortlist(shortlist) {
                const el = document.getElementById('shortlistList');
                if (!el) return;
                if (!shortlist || !shortlist.length) {
                    el.innerHTML = '<p class="text-xs" style="color: var(--paper-faint);">No shortlisted players yet — star players on the Scouts page.</p>';
                    return;
                }
                el.innerHTML = shortlist.map(p => `
                    <div class="card-section p-3" style="${p.is_current_lot ? 'border-left: 3px solid var(--gold);' : ''}">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold" style="color: var(--paper);">${p.name}</p>
                            ${p.is_current_lot ? '<span class="tag gold" style="font-size:0.55rem;">Now Bidding</span>' : ''}
                        </div>
                        <p class="text-xs" style="color: var(--paper-faint);">${p.role} — ${p.country}</p>
                        <p class="text-xs font-semibold mt-1" style="color: var(--gold);">${fmtMoney(p.base_value, 13)}</p>
                    </div>
                `).join('');
            }

            function renderSeats(participants) {
                const list = document.getElementById('seatsList');
                if (!list) return;
                if (participants.length === 0) {
                    list.innerHTML = '<p class="text-xs" style="color: var(--paper-faint);">No one seated yet</p>';
                    return;
                }
                list.innerHTML = participants.map(p => {
                    const avatarSrc = p.manager_avatar || null;
                    const crestSrc = p.team_logo || null;
                    return `
                        <div class="card-section p-3 flex items-center gap-3 ${p.is_online ? 'seat-online' : 'seat-away'}">
                            <div class="seat-avatar">
                                ${avatarSrc ? `<img src="${avatarSrc}" alt="${p.manager_name || ''}">` : `<div class="initials" style="font-size:0.85rem;">${(p.manager_name || '?').substring(0,2).toUpperCase()}</div>`}
                                <span class="seat-dot"></span>
                                ${crestSrc ? `<span class="seat-crest"><img src="${crestSrc}" alt=""></span>` : ''}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate" style="color: var(--paper);">${p.manager_name || 'Manager'}${p.is_you ? ' (You)' : ''}</p>
                                <p class="text-xs truncate" style="color: var(--paper-faint);">${p.team_short_name || p.team_name || ''}</p>
                                ${p.is_leading ? '<span class="tag gold" style="font-size:0.6rem;">Leading</span>' : ''}
                                ${!p.is_leading && p.has_passed ? '<span class="tag passed" style="font-size:0.6rem;">Passed</span>' : ''}
                                ${!p.is_online ? '<span class="text-xs" style="color: var(--paper-faint);"> — away</span>' : ''}
                            </div>
                        </div>
                    `;
                }).join('');
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
                const mins = Math.floor(remaining / 60);
                const secs = remaining % 60;
                ring.textContent = mins + ':' + String(secs).padStart(2, '0');
                ring.classList.toggle('urgent', remaining <= 15);
            }
            setInterval(tickCountdown, 1000);
            tickCountdown();

            function updateLiveStatus(data) {
                if (!window.setLiveStatus) return;
                if (data.status === 'paused') {
                    window.setLiveStatus('Bidding is paused by the auctioneer — stay seated, it will resume shortly.', false);
                    return;
                }
                if (!data.you) {
                    window.setLiveStatus(`Bidding is live on <strong>${data.player_name || 'this lot'}</strong>.`, false);
                    return;
                }
                if (data.time_expired) {
                    window.setLiveStatus("Time's up — waiting for the sale to be called.", true);
                } else if (data.you.is_leading) {
                    window.setLiveStatus("You're leading this bid — sit tight to see if anyone tops it.", false);
                } else if (data.you.has_passed) {
                    window.setLiveStatus("You've passed — you'll get another chance if someone bids higher.", false);
                } else if (!data.you.has_squad_space) {
                    window.setLiveStatus("Your squad is full — you can watch, but can't bid on this lot.", false);
                } else {
                    window.setLiveStatus('Your turn to act — outbid the leader or pass.', true);
                }
            }

            function applyState(data) {
                document.getElementById('statusLabel').textContent =
                    data.status === 'live' ? 'Live Now' : (data.status === 'paused' ? 'Paused' : data.status);
                updateLiveStatus(data);
                renderShortlist(data.shortlist || []);
                const badge = document.getElementById('shortlistBadge');
                if (badge) badge.innerHTML = data.is_shortlisted ? '<svg width="22" height="22" viewBox="0 0 24 24" fill="var(--gold)" stroke="var(--gold)" stroke-width="1.5" style="display:inline-block; vertical-align:-3px;" title="On your shortlist"><polygon points="12 2 15.09 8.63 22 9.24 16.5 14.14 18.18 21 12 17.27 5.82 21 7.5 14.14 2 9.24 8.91 8.63 12 2"/></svg>' : '';

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
                const passBtn = document.getElementById('passBtn');
                const passedNotice = document.getElementById('passedNotice');
                if (expiredNotice) {
                    expiredNotice.classList.toggle('hidden', !data.time_expired);
                }
                if (data.you) {
                    const disableBid = data.time_expired || data.you.is_leading || !data.you.has_squad_space || data.you.has_passed;
                    if (bidMinBtn) bidMinBtn.disabled = disableBid;
                    if (bidCustomBtn) bidCustomBtn.disabled = disableBid;
                    if (passBtn) passBtn.disabled = data.you.is_leading || data.you.has_passed || !data.leader;
                    if (passedNotice) passedNotice.style.display = (data.you.has_passed && !data.you.is_leading) ? 'block' : 'none';
                }

                renderSeats(data.participants || []);

                if (data.status !== 'live' && data.status !== 'paused') {
                    polling = false;
                    showConclusionOverlay(data);
                } else if (isPausedInitial !== (data.status === 'paused')) {
                    window.location.reload();
                }
            }

            function coloredCrestBg(leader) {
                const c1 = (leader && leader.primary_color) || '#1D4ED8';
                const c2 = (leader && leader.secondary_color) || '#0D1220';
                return `linear-gradient(155deg, ${c1} 0%, ${c2} 85%)`;
            }

            function showConclusionOverlay(data) {
                const overlay = document.getElementById('soldOverlay');
                if (!overlay) { window.location.reload(); return; }

                if (data.sale_result === 'sold' && data.leader) {
                    const leader = data.leader;
                    overlay.innerHTML = `
                        <div class="overlay-bg" style="background: ${coloredCrestBg(leader)};"></div>
                        ${leader.team_logo ? `<div class="overlay-logo-watermark"><img src="${leader.team_logo}" alt=""></div>` : ''}
                        <div class="overlay-card">
                            <p class="overlay-sold-tag">Sold!</p>
                            <div class="overlay-avatars">
                                <div class="overlay-crest">${leader.team_logo ? `<img src="${leader.team_logo}" alt="">` : ''}</div>
                                <div class="overlay-avatar">${leader.manager_avatar ? `<img src="${leader.manager_avatar}" alt="">` : ''}</div>
                            </div>
                            <p class="overlay-player">${data.player_name || ''}</p>
                            <p class="overlay-price">${fmtMoney(data.current_bid, 22)}</p>
                            <p class="overlay-team">Won by ${leader.team_name}</p>
                            <p class="overlay-manager">Managed by ${leader.manager_name || '—'}</p>
                            <button type="button" class="overlay-continue" onclick="continueAfterAuction()">Continue</button>
                        </div>
                    `;
                } else if (data.sale_result === 'unsold') {
                    overlay.innerHTML = `
                        <div class="overlay-bg" style="background: linear-gradient(155deg, #313C5C 0%, #0D1220 85%);"></div>
                        <div class="overlay-card">
                            <p class="overlay-sold-tag">Unsold</p>
                            <p class="overlay-player">${data.player_name || ''}</p>
                            <p class="overlay-manager" style="margin-bottom:2rem;">No winning bid was finalized for this lot.</p>
                            <button type="button" class="overlay-continue" onclick="continueAfterAuction()">Continue</button>
                        </div>
                    `;
                } else {
                    window.location.reload();
                    return;
                }

                overlay.classList.add('is-visible');
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

            const passBtn = document.getElementById('passBtn');
            if (passBtn) passBtn.addEventListener('click', () => {
                const errorEl = document.getElementById('bidError');
                if (errorEl) errorEl.textContent = '';
                fetch(passUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ _token: csrf }).toString(),
                }).then(async r => {
                    const data = await r.json();
                    if (!r.ok) {
                        if (errorEl) errorEl.textContent = data.error || 'Could not pass.';
                        return;
                    }
                    poll();
                }).catch(() => {
                    if (errorEl) errorEl.textContent = 'Network error — try again.';
                });
            });

            // Video/voice call — opens in its own popup window so it
            // keeps running independent of whatever page this tab is on.
            const callBtn = document.getElementById('callBtn');
            const callUrl = @json(route('manager.call', $auctionSession->auction_draft_id ? 'draft-' . $auctionSession->auction_draft_id : 'auction-' . $auctionSession->id));
            let callWindow = null;
            if (callBtn) {
                callBtn.addEventListener('click', () => {
                    if (callWindow && !callWindow.closed) {
                        callWindow.focus();
                        return;
                    }
                    callWindow = window.open(callUrl, 'ESLCall', 'width=440,height=680,resizable=yes,noopener=no');
                });
            }
        })();
        </script>
    @endif
@endsection
