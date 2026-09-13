@extends('layouts.auction-room')

@section('title', 'Country Draft')

@section('head')
    <style>
        /* ---------- Split-room layout: stage + sidebar, side by side ---------- */
        .draft-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; align-items: start; }
        @media (min-width: 1024px) {
            .draft-grid { grid-template-columns: minmax(0, 1fr) 320px; }
            .draft-sidebar { position: sticky; top: 1.5rem; }
        }
        .sidebar-tabs { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
        .sidebar-tab { flex: 1; padding: 0.55rem 0.5rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; border: 1px solid var(--line); background: transparent; color: var(--paper-faint); cursor: pointer; border-radius: 3px; transition: color 150ms, border-color 150ms; }
        .sidebar-tab.is-active { border-color: var(--gold); color: var(--gold); }
        .sidebar-panel.hidden { display: none; }

        .seat-avatar { position: relative; width: 52px; height: 52px; border-radius: 9999px; overflow: hidden; border: 2px solid var(--line-strong); background-color: var(--surface-raised); flex-shrink: 0; }
        .seat-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .seat-crest { position: absolute; bottom: -4px; right: -4px; width: 20px; height: 20px; border-radius: 9999px; border: 2px solid var(--surface); background-color: var(--surface-raised); overflow: hidden; }
        .seat-crest img { width: 100%; height: 100%; object-fit: cover; }
        .seat-dot { position: absolute; top: -2px; right: -2px; width: 11px; height: 11px; border-radius: 9999px; background-color: var(--up); border: 2px solid var(--surface); }
        .turn-card { display: flex; align-items: center; gap: 0.65rem; text-align: left; opacity: 0.55; padding: 0.5rem; border-radius: 6px; transition: opacity 200ms, background-color 200ms; }
        .turn-card.is-active { opacity: 1; background-color: var(--surface-raised); }
        .turn-card.is-away .seat-avatar { filter: grayscale(70%); opacity: 0.5; }
        .turn-card.is-away .seat-dot { background-color: var(--paper-faint); }
        .turn-card .role-tag { font-size: 0.55rem; display: inline-block; }
        @keyframes spinCycle { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
        .spinning { animation: spinCycle 120ms linear infinite; }
        .tag.passed { border-color: var(--paper-faint); color: var(--paper-faint); }

        /* ---------- Player nomination cards: 3D tilt on hover ---------- */
        .player-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; perspective: 1000px; }
        .player-card { position: relative; border: 1px solid var(--line); border-radius: 8px; padding: 1rem; background: var(--surface-raised); transition: transform 150ms ease-out, box-shadow 200ms ease-out; transform-style: preserve-3d; will-change: transform; }
        .player-card.is-tiltable:hover { box-shadow: 0 24px 48px rgba(0,0,0,0.4), 0 0 0 1px var(--gold-dim); z-index: 2; }
        .player-card-portrait { width: 100%; aspect-ratio: 3 / 4; border-radius: 6px; overflow: hidden; background: var(--surface); margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: center; transform: translateZ(20px); }
        .player-card-portrait img { width: 100%; height: 100%; object-fit: cover; }
        .player-card-portrait .initials { font-size: 1.4rem; }
        .player-card-name { font-size: 0.85rem; font-weight: 600; color: var(--paper); line-height: 1.2; margin-bottom: 0.15rem; }
        .player-card-meta { font-size: 0.65rem; color: var(--paper-faint); margin-bottom: 0.6rem; }
        .player-card-value { font-size: 0.85rem; font-weight: 700; color: var(--gold); margin-bottom: 0.75rem; }
        .player-card .nominate-btn { width: 100%; }

        @keyframes bidFlash { 0% { background-color: rgba(232, 178, 61, 0.35); } 100% { background-color: transparent; } }
        @keyframes leaderPop { 0% { transform: scale(0.85); opacity: 0.4; } 60% { transform: scale(1.06); opacity: 1; } 100% { transform: scale(1); opacity: 1; } }
        .bid-flash { animation: bidFlash 900ms ease-out; }
        .leader-pop { animation: leaderPop 420ms cubic-bezier(.2,.9,.3,1.3); }
        .countdown-ring { width: 72px; height: 72px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; border: 4px solid var(--line-strong); font-family: 'Barlow Condensed', sans-serif; font-weight: 700; font-size: 1.4rem; color: var(--paper); transition: border-color 200ms, color 200ms; margin: 0 auto; }
        .countdown-ring.urgent { border-color: var(--live); color: var(--live); }
        .leader-crest-avatar { display: flex; align-items: center; justify-content: center; }
        .leader-crest-avatar .crest-box, .leader-crest-avatar .avatar-box { width: 56px; height: 56px; border-radius: 9999px; overflow: hidden; border: 2px solid var(--line-strong); background-color: var(--surface-raised); }
        .leader-crest-avatar .avatar-box { margin-left: -14px; border-color: var(--gold); }
        .leader-crest-avatar img { width: 100%; height: 100%; object-fit: cover; }

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
    @if (! $draft)
        <div class="card-section p-12 text-center">
            <p class="eyebrow gold mb-3">Country Draft</p>
            <p class="text-sm mb-6" style="color: var(--paper-faint);">No draft is currently in progress.</p>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.draft.create') }}" class="btn-accent px-8 py-3">Start a Draft</a>
            @endif
        </div>
    @else
        <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
            <div>
                <p class="eyebrow gold mb-2">Country Draft — One Room</p>
                <h1 class="font-display text-4xl md:text-5xl font-semibold mb-2" style="color: var(--paper);" id="countryHeading">
                    {{ $draft->current_country ?? 'Awaiting Next Country' }}
                </h1>
                <p class="text-sm" style="color: var(--paper-faint);" id="statusLine">&nbsp;</p>
            </div>
            <div class="text-center">
                <div id="timerSlot"></div>
            </div>
        </div>

        <div class="card-section p-4 mb-6">
            <p class="text-xs" style="color: var(--paper-faint);">
                <strong style="color: var(--paper-dim);">How turns work:</strong>
                the manager marked <span class="tag gold" style="font-size:0.6rem;">Spins Next</span> spins a random country; the manager after them in order gets first pick from it. Picking rotates through everyone in order — nominate a player (opens bidding at their base value) or skip your turn. Once a country runs out of players, or everyone skips in a row, it's retired and the <em>next</em> manager in order spins for the next country. The current turn order and the live call stay one click away in the panel on the right — nothing here ever swaps out from under you.
            </p>
        </div>

        <div class="draft-grid">
            <div class="draft-stage" id="mainPanel"></div>

            <aside class="draft-sidebar">
                <div class="sidebar-tabs">
                    <button type="button" class="sidebar-tab is-active" data-sidebar-tab="order">Turn Order</button>
                    <button type="button" class="sidebar-tab" data-sidebar-tab="call">Live Call</button>
                </div>

                <div class="sidebar-panel" id="sidebarOrder">
                    <p class="eyebrow mb-2" style="color: var(--paper-faint);">Draft Order</p>
                    <div id="turnStrip" class="flex flex-col gap-2"></div>
                    <div id="burnedRow" class="mt-4"></div>
                </div>

                <div class="sidebar-panel hidden" id="sidebarCall">
                    <div class="card-section p-4">
                        <p class="eyebrow gold mb-1">Talk While You Draft</p>
                        <p class="text-xs mb-4" style="color: var(--paper-faint);">Opens a free video &amp; voice call in its own window (powered by Jitsi Meet — a public third-party service, not hosted by ESL Cricket). It keeps running even if you navigate around the rest of the site in this tab.</p>
                        <button type="button" id="callBtn" class="btn-accent px-5 py-2.5 w-full">Start Video / Voice Call</button>
                    </div>
                </div>
            </aside>
        </div>

        <div id="soldOverlay" class="hidden"></div>
    @endif

    @if ($draft)
        <script src="https://meet.jit.si/external_api.js"></script>
        <script>
        (function () {
            const draftId = {{ $draft->id }};
            const stateUrl = @json(route('manager.draft.state', $draft));
            const spinUrl = @json(route('manager.draft.spin', $draft));
            const nominateUrl = @json(route('manager.draft.nominate', $draft));
            const skipUrl = @json(route('manager.draft.skip', $draft));
            const callUrl = @json(route('manager.call', 'draft-' . $draft->id));
            const bidUrlTemplate = @json(route('manager.auction.bid', ['auctionSession' => '__ID__']));
            const passUrlTemplate = @json(route('manager.auction.pass', ['auctionSession' => '__ID__']));
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            let spinning = false;
            let lastBid = null;
            let lastLeaderId = 'unset';
            let deadline = null;
            let currentAuctionId = null;
            let overlayShownFor = null;

            function post(url, body) {
                return fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(Object.assign({ _token: csrf }, body || {})).toString(),
                }).then(async r => {
                    let data;
                    try { data = await r.json(); } catch (e) { data = { error: 'Something went wrong — please refresh and try again.' }; }
                    return { ok: r.ok, data };
                }).catch(() => ({ ok: false, data: { error: 'Network error — please refresh and try again.' } }));
            }

            function coinSvg(size) {
                return `<svg width="${size}" height="${size}" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display:inline-block; vertical-align:-2px;"><circle cx="16" cy="16" r="15" fill="var(--gold)" stroke="var(--gold-dim)" stroke-width="1.5"/><circle cx="16" cy="16" r="10.5" fill="none" stroke="var(--gold-dim)" stroke-width="1.25"/><text x="16" y="21" font-family="Georgia, serif" font-size="14" font-weight="700" text-anchor="middle" fill="var(--gold-dim)">E</text></svg>`;
            }
            function fmtMoney(n, size) {
                size = size || 20;
                return `<span class="inline-flex items-center gap-1.5">${coinSvg(size)}<span>${Math.round(n).toLocaleString('en-US')}</span></span>`;
            }

            // ---------- Video / voice (popup window, survives navigation) ----------
            const callBtn = document.getElementById('callBtn');
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

            // ---------- Sidebar tabs (Turn Order / Live Call) ----------
            document.querySelectorAll('.sidebar-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('is-active'));
                    tab.classList.add('is-active');
                    document.getElementById('sidebarOrder').classList.toggle('hidden', tab.dataset.sidebarTab !== 'order');
                    document.getElementById('sidebarCall').classList.toggle('hidden', tab.dataset.sidebarTab !== 'call');
                });
            });

            // ---------- Turn strip ----------
            function renderTeamCard(team, roleLabel, isActive, bidInfo) {
                if (!team) return '';
                const isAway = bidInfo && !bidInfo.is_online;
                return `
                    <div class="turn-card ${isActive ? 'is-active' : ''} ${isAway ? 'is-away' : ''}">
                        <div class="seat-avatar">
                            ${team.manager_avatar ? `<img src="${team.manager_avatar}" alt="">` : `<div class="initials">${(team.manager_name || '?').substring(0,2).toUpperCase()}</div>`}
                            ${bidInfo ? '<span class="seat-dot"></span>' : ''}
                            ${team.team_logo ? `<span class="seat-crest"><img src="${team.team_logo}" alt=""></span>` : ''}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold truncate" style="color: var(--paper);">${team.manager_name || ''}</p>
                            <p class="text-xs truncate" style="color: var(--paper-faint);">${team.team_short_name || ''}</p>
                            <div class="flex flex-wrap gap-1 mt-1">
                                ${roleLabel ? `<span class="tag gold role-tag">${roleLabel}</span>` : ''}
                                ${bidInfo && bidInfo.is_leading ? '<span class="tag gold role-tag">Leading</span>' : ''}
                                ${bidInfo && !bidInfo.is_leading && bidInfo.has_passed ? '<span class="tag passed role-tag">Passed</span>' : ''}
                            </div>
                        </div>
                    </div>
                `;
            }

            function renderTurnStrip(data) {
                const strip = document.getElementById('turnStrip');
                if (!strip) return;
                const bidByTeam = {};
                if (data.auction) {
                    (data.auction.participants || []).forEach(p => { bidByTeam[p.team_id] = p; });
                }
                strip.innerHTML = data.turn_order.map(team => {
                    const roles = [];
                    if (!data.auction) {
                        if (data.country_picker && team.team_id === data.country_picker.team_id) roles.push('Spins Next');
                        if (data.active_picker && team.team_id === data.active_picker.team_id) roles.push('Picking Now');
                    }
                    const isActive = roles.length > 0 || (data.auction && bidByTeam[team.team_id] && bidByTeam[team.team_id].is_leading);
                    return renderTeamCard(team, roles.join(' / '), isActive, bidByTeam[team.team_id] || null);
                }).join('');
            }

            function renderBurned(countries) {
                const row = document.getElementById('burnedRow');
                if (!row) return;
                if (!countries.length) { row.innerHTML = ''; return; }
                row.innerHTML = '<p class="eyebrow mb-2" style="color: var(--paper-faint);">Retired Countries</p><div class="flex flex-wrap gap-2">' +
                    countries.map(c => `<span class="tag" style="opacity:0.6;">${c}</span>`).join('') + '</div>';
            }

            // ---------- Nominate / skip panel ----------
            function renderPlayerList(players, isYourTurn) {
                if (!players.length) {
                    return '<p class="text-sm" style="color: var(--paper-faint);">No available players left from this country.</p>';
                }
                return `<div class="player-grid">` + players.map(p => `
                    <div class="player-card is-tiltable">
                        <div class="player-card-portrait">
                            ${p.image ? `<img src="${p.image}" alt="">` : `<div class="initials">${(p.name || '?').substring(0,2).toUpperCase()}</div>`}
                        </div>
                        <p class="player-card-name">${p.name}</p>
                        <p class="player-card-meta">${p.role} — ${p.tier}</p>
                        <p class="player-card-value">${fmtMoney(p.base_value, 16)}</p>
                        ${isYourTurn ? `<button type="button" class="btn-accent py-2 text-xs nominate-btn" data-id="${p.id}">Nominate</button>` : ''}
                    </div>
                `).join('') + `</div>`;
            }

            // 3D tilt-on-hover for player cards — purely cosmetic, degrades
            // to a flat card if the pointer never moves over it.
            function attachTilt(container) {
                container.querySelectorAll('.player-card.is-tiltable').forEach(card => {
                    card.addEventListener('mousemove', (e) => {
                        const r = card.getBoundingClientRect();
                        const px = (e.clientX - r.left) / r.width;
                        const py = (e.clientY - r.top) / r.height;
                        const rotY = (px - 0.5) * 16;
                        const rotX = (0.5 - py) * 16;
                        card.style.transform = `perspective(800px) rotateX(${rotX}deg) rotateY(${rotY}deg) scale(1.05)`;
                    });
                    card.addEventListener('mouseleave', () => { card.style.transform = ''; });
                });
            }

            function setStatusLine(html) {
                const el = document.getElementById('statusLine');
                if (el) el.innerHTML = html;
            }

            function renderNominatePanel(data) {
                const panel = document.getElementById('mainPanel');
                document.getElementById('timerSlot').innerHTML = '';

                if (data.is_your_turn_to_pick) {
                    setStatusLine(`Your turn — nominate a player from ${data.current_country}, or skip.`);
                } else {
                    setStatusLine(`Waiting for <strong style="color: var(--paper-dim);">${data.active_picker ? data.active_picker.manager_name : 'the next manager'}</strong> to pick from ${data.current_country}.`);
                }

                const banner = data.is_your_turn_to_pick
                    ? `<div class="card-section p-4 mb-4" style="border-left: 3px solid var(--gold);"><p class="text-sm font-semibold" style="color: var(--paper);">Your turn — pick a player below, or skip.</p></div>`
                    : `<div class="card-section p-4 mb-4" style="color: var(--paper-faint);"><p class="text-sm">Waiting for <strong style="color: var(--paper-dim);">${data.active_picker ? data.active_picker.manager_name : 'the next manager'}</strong> to pick — you'll get a turn once they nominate or skip. (Nominate buttons only appear for the manager whose turn it is.)</p></div>`;

                panel.innerHTML = `
                    ${banner}
                    <div class="card-section mb-4">
                        ${renderPlayerList(data.available_players, data.is_your_turn_to_pick)}
                    </div>
                    ${data.is_your_turn_to_pick ? `<div class="text-center"><button type="button" id="skipBtn" class="btn-ghost px-8 py-3">Skip My Turn</button></div>` : ''}
                    <p id="nominateError" class="text-xs text-center mt-3" style="color: var(--live);"></p>
                `;
                document.querySelectorAll('.nominate-btn').forEach(b => b.addEventListener('click', () => doNominate(b.dataset.id)));
                attachTilt(panel);
                const skipBtn = document.getElementById('skipBtn');
                if (skipBtn) skipBtn.addEventListener('click', doSkip);
            }

            function renderSpinPanel(data) {
                const panel = document.getElementById('mainPanel');
                document.getElementById('timerSlot').innerHTML = '';
                if (data.is_your_turn_to_spin) {
                    setStatusLine('Your turn — spin to reveal the next country.');
                    panel.innerHTML = `<div class="card-section p-12 text-center"><button type="button" id="spinBtn" class="btn-accent px-10 py-4 text-lg">Spin For Country</button></div>`;
                    document.getElementById('spinBtn').addEventListener('click', doSpin);
                } else {
                    setStatusLine(`Waiting for <strong style="color: var(--paper-dim);">${data.country_picker ? data.country_picker.manager_name : 'the next manager'}</strong> to spin for a country.`);
                    panel.innerHTML = `<div class="card-section p-12 text-center" style="color: var(--paper-faint);">Waiting for ${data.country_picker ? data.country_picker.manager_name : 'the next manager'} to spin for a country.</div>`;
                }
            }

            function renderCompletedPanel() {
                setStatusLine('Every country has been drafted.');
                document.getElementById('timerSlot').innerHTML = '';
                document.getElementById('mainPanel').innerHTML = `<div class="card-section p-12 text-center"><p class="eyebrow gold mb-3">Draft Complete</p><p class="text-sm" style="color: var(--paper-faint);">All available players have been auctioned. Head to Auctions to review results.</p></div>`;
            }

            // ---------- Embedded bidding panel ----------
            function tickCountdown() {
                const ring = document.getElementById('countdownRing');
                if (!ring) return;
                if (!deadline) { ring.textContent = '—'; return; }
                const remaining = Math.max(0, Math.round((new Date(deadline) - new Date()) / 1000));
                const mins = Math.floor(remaining / 60);
                const secs = remaining % 60;
                ring.textContent = mins + ':' + String(secs).padStart(2, '0');
                ring.classList.toggle('urgent', remaining <= 15);
            }
            setInterval(tickCountdown, 1000);

            function renderLeader(leader) {
                const el = document.getElementById('leaderDisplay');
                const nameEl = document.getElementById('leaderName');
                if (!el) return;
                if (!leader) {
                    el.innerHTML = '<p class="text-sm" style="color: var(--paper-faint);">No bids yet — be the first.</p>';
                    if (nameEl) nameEl.textContent = ' ';
                    return;
                }
                el.innerHTML = `
                    <div class="leader-crest-avatar leader-pop">
                        <div class="crest-box">${leader.team_logo ? `<img src="${leader.team_logo}" alt="">` : ''}</div>
                        <div class="avatar-box">${leader.manager_avatar ? `<img src="${leader.manager_avatar}" alt="">` : ''}</div>
                    </div>
                `;
                if (nameEl) nameEl.textContent = 'Leading — ' + leader.team_name + (leader.manager_name ? ' (' + leader.manager_name + ')' : '');
            }

            function renderBiddingPanel(auction, player) {
                document.getElementById('timerSlot').innerHTML = `<div class="countdown-ring" id="countdownRing">—</div><p class="text-xs mt-1" style="color: var(--paper-faint);">time left</p>`;

                const panel = document.getElementById('mainPanel');
                panel.innerHTML = `
                    <div class="flex items-start gap-6 mb-6">
                        <div class="player-portrait" style="width: 100px; height: 130px; flex-shrink: 0;">
                            <div class="initials">${(auction.player_name || '?').substring(0,2).toUpperCase()}</div>
                        </div>
                        <div>
                            <p class="eyebrow live mb-1">Now Bidding</p>
                            <h2 class="font-display text-2xl font-semibold" style="color: var(--paper);">${auction.player_name || ''}</h2>
                        </div>
                    </div>
                    <div class="masthead text-center">
                        <p class="stat-caption mb-3">Current Bid</p>
                        <div class="flex items-center justify-center mb-4" id="leaderDisplay">
                            <p class="text-sm" style="color: var(--paper-faint);">No bids yet — be the first.</p>
                        </div>
                        <p class="stat-figure gold" id="currentBidFigure" style="font-size: 2.25rem;">${fmtMoney(auction.current_bid, 26)}</p>
                        <p class="text-sm mt-2" id="leaderName" style="color: var(--paper-dim);">&nbsp;</p>

                        <div class="mt-6 pt-6" style="border-top: var(--rule);">
                            <div id="timeExpiredNotice" class="hidden mb-3"><span class="status-pill live">Time's Up — Waiting For The Call</span></div>
                            <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                                <button type="button" id="bidMinBtn" class="btn-accent px-8 py-3 text-base">Bid <span id="minBidLabel"></span></button>
                                <div class="flex items-center gap-2">
                                    <input type="number" id="customBidInput" class="field px-3 py-2 text-sm" style="width: 10rem;" placeholder="Custom amount">
                                    <button type="button" id="bidCustomBtn" class="btn-ghost px-4 py-2 text-sm">Bid This</button>
                                </div>
                                <button type="button" id="passBtn" class="btn-ghost px-6 py-3 text-base" style="color: var(--live); border-color: var(--live);">Pass</button>
                            </div>
                            <p class="text-xs mt-2" id="passedNotice" style="color: var(--paper-faint); display:none;">You've passed on this bid — you'll get another chance if someone bids higher.</p>
                            <p class="text-xs mt-3" id="bidError" style="color: var(--live);"></p>
                        </div>
                    </div>
                `;

                const bidMinBtn = document.getElementById('bidMinBtn');
                const bidCustomBtn = document.getElementById('bidCustomBtn');
                const passBtn = document.getElementById('passBtn');
                if (bidMinBtn) bidMinBtn.addEventListener('click', () => placeBid(auction.session_id, null));
                if (bidCustomBtn) bidCustomBtn.addEventListener('click', () => {
                    const val = document.getElementById('customBidInput').value;
                    if (!val || Number(val) <= 0) { document.getElementById('bidError').textContent = 'Enter a valid amount.'; return; }
                    placeBid(auction.session_id, val);
                });
                if (passBtn) passBtn.addEventListener('click', () => doPass(auction.session_id));
            }

            function applyBiddingState(auction) {
                if (auction.session_id !== currentAuctionId) {
                    // A different lot just opened — (re)build the panel fresh.
                    currentAuctionId = auction.session_id;
                    lastBid = null;
                    lastLeaderId = 'unset';
                    renderBiddingPanel(auction, auction.player_name);
                }

                if (auction.you) {
                    if (auction.time_expired) {
                        setStatusLine("Time's up — waiting for the sale to be called.");
                    } else if (auction.you.is_leading) {
                        setStatusLine('You\'re leading this bid — sit tight to see if anyone tops it.');
                    } else if (auction.you.has_passed) {
                        setStatusLine("You've passed — you'll get another chance if someone bids higher.");
                    } else if (!auction.you.has_squad_space) {
                        setStatusLine('Your squad is full — you can watch, but can\'t bid on this lot.');
                    } else {
                        setStatusLine('Bidding is live — outbid the leader or pass.');
                    }
                } else {
                    setStatusLine('Bidding is live.');
                }

                deadline = auction.deadline_at;
                tickCountdown();

                if (auction.current_bid !== lastBid) {
                    const fig = document.getElementById('currentBidFigure');
                    if (fig) {
                        fig.innerHTML = fmtMoney(auction.current_bid, 26);
                        fig.classList.remove('bid-flash'); void fig.offsetWidth; fig.classList.add('bid-flash');
                    }
                    lastBid = auction.current_bid;
                }

                if (auction.leader && auction.leader.team_id !== lastLeaderId) {
                    renderLeader(auction.leader);
                    lastLeaderId = auction.leader.team_id;
                } else if (!auction.leader && lastLeaderId !== null) {
                    renderLeader(null);
                    lastLeaderId = null;
                }

                const minLabel = document.getElementById('minBidLabel');
                if (minLabel) minLabel.innerHTML = fmtMoney(auction.minimum_bid, 14);
                const customInput = document.getElementById('customBidInput');
                if (customInput) customInput.min = Math.ceil(auction.minimum_bid);

                const expiredNotice = document.getElementById('timeExpiredNotice');
                const bidMinBtn = document.getElementById('bidMinBtn');
                const bidCustomBtn = document.getElementById('bidCustomBtn');
                const passBtn = document.getElementById('passBtn');
                const passedNotice = document.getElementById('passedNotice');
                if (expiredNotice) expiredNotice.classList.toggle('hidden', !auction.time_expired);
                if (auction.you) {
                    const disableBid = auction.time_expired || auction.you.is_leading || !auction.you.has_squad_space || auction.you.has_passed;
                    if (bidMinBtn) bidMinBtn.disabled = disableBid;
                    if (bidCustomBtn) bidCustomBtn.disabled = disableBid;
                    if (passBtn) passBtn.disabled = auction.you.is_leading || auction.you.has_passed || !auction.leader;
                    if (passedNotice) passedNotice.style.display = (auction.you.has_passed && !auction.you.is_leading) ? 'block' : 'none';
                }
            }

            function coloredCrestBg(leader) {
                const c1 = (leader && leader.primary_color) || '#1D4ED8';
                const c2 = (leader && leader.secondary_color) || '#0D1220';
                return `linear-gradient(155deg, ${c1} 0%, ${c2} 85%)`;
            }

            function showConclusionOverlay(auction) {
                if (overlayShownFor === auction.session_id) return;
                overlayShownFor = auction.session_id;

                const overlay = document.getElementById('soldOverlay');
                if (!overlay) return;

                if (auction.sale_result === 'sold' && auction.leader) {
                    const leader = auction.leader;
                    overlay.innerHTML = `
                        <div class="overlay-bg" style="background: ${coloredCrestBg(leader)};"></div>
                        ${leader.team_logo ? `<div class="overlay-logo-watermark"><img src="${leader.team_logo}" alt=""></div>` : ''}
                        <div class="overlay-card">
                            <p class="overlay-sold-tag">Sold!</p>
                            <div class="overlay-avatars">
                                <div class="overlay-crest">${leader.team_logo ? `<img src="${leader.team_logo}" alt="">` : ''}</div>
                                <div class="overlay-avatar">${leader.manager_avatar ? `<img src="${leader.manager_avatar}" alt="">` : ''}</div>
                            </div>
                            <p class="overlay-player">${auction.player_name || ''}</p>
                            <p class="overlay-price">${fmtMoney(auction.current_bid, 22)}</p>
                            <p class="overlay-team">Won by ${leader.team_name}</p>
                            <p class="overlay-manager">Managed by ${leader.manager_name || '—'}</p>
                            <button type="button" class="overlay-continue" id="overlayContinueBtn">Continue</button>
                        </div>
                    `;
                } else {
                    overlay.innerHTML = `
                        <div class="overlay-bg" style="background: linear-gradient(155deg, #313C5C 0%, #0D1220 85%);"></div>
                        <div class="overlay-card">
                            <p class="overlay-sold-tag">Unsold</p>
                            <p class="overlay-player">${auction.player_name || ''}</p>
                            <p class="overlay-manager" style="margin-bottom:2rem;">No winning bid was finalized for this lot.</p>
                            <button type="button" class="overlay-continue" id="overlayContinueBtn">Continue</button>
                        </div>
                    `;
                }

                overlay.classList.add('is-visible');
                document.getElementById('overlayContinueBtn').addEventListener('click', () => {
                    overlay.classList.remove('is-visible');
                    currentAuctionId = null;
                    poll();
                });
            }

            // ---------- Master render ----------
            function applyState(data) {
                renderTurnStrip(data);
                renderBurned(data.burned_countries || []);

                if (data.status === 'completed') {
                    renderCompletedPanel();
                    return;
                }

                if (data.auction && in_array_status(data.auction.status)) {
                    applyBiddingState(data.auction);
                    return;
                }

                if (data.auction && (data.auction.status === 'completed' || data.auction.status === 'cancelled')) {
                    showConclusionOverlay(data.auction);
                    return;
                }

                currentAuctionId = null;

                if (!data.current_country) {
                    renderSpinPanel(data);
                    return;
                }

                renderNominatePanel(data);
            }

            function in_array_status(status) {
                return status === 'live' || status === 'paused';
            }

            function poll() {
                fetch(stateUrl, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(applyState)
                    .catch(() => {});
            }
            poll();
            setInterval(poll, 2000);

            // ---------- Actions ----------
            function doSpin() {
                const btn = document.getElementById('spinBtn');
                if (btn) { btn.disabled = true; btn.classList.add('spinning'); }
                spinning = true;
                const heading = document.getElementById('countryHeading');
                const sample = ['Pakistan', 'India', 'Australia', 'England', 'South Africa', 'New Zealand', 'Afghanistan', 'Sri Lanka', 'West Indies', 'Bangladesh'];
                let ticks = 0;
                const cycle = setInterval(() => {
                    heading.textContent = sample[Math.floor(Math.random() * sample.length)];
                    ticks++;
                    if (ticks > 12) clearInterval(cycle);
                }, 90);
                post(spinUrl).then(({ ok, data }) => {
                    setTimeout(() => {
                        clearInterval(cycle);
                        spinning = false;
                        if (!ok) alert(data.error || 'Could not spin.');
                        poll();
                    }, 1100);
                });
            }

            function doNominate(playerId) {
                const errorEl = document.getElementById('nominateError');
                if (errorEl) errorEl.textContent = '';
                post(nominateUrl, { player_id: playerId }).then(({ ok, data }) => {
                    if (!ok) { if (errorEl) errorEl.textContent = data.error || 'Could not nominate.'; return; }
                    poll();
                });
            }

            function doSkip() {
                post(skipUrl).then(({ ok, data }) => {
                    if (!ok) alert(data.error || 'Could not skip.');
                    poll();
                });
            }

            function placeBid(sessionId, amount) {
                const errorEl = document.getElementById('bidError');
                if (errorEl) errorEl.textContent = '';
                const url = bidUrlTemplate.replace('__ID__', sessionId);
                const body = amount ? { amount } : {};
                post(url, body).then(({ ok, data }) => {
                    if (!ok) { if (errorEl) errorEl.textContent = data.error || 'Could not place bid.'; return; }
                    poll();
                });
            }

            function doPass(sessionId) {
                const errorEl = document.getElementById('bidError');
                if (errorEl) errorEl.textContent = '';
                const url = passUrlTemplate.replace('__ID__', sessionId);
                post(url).then(({ ok, data }) => {
                    if (!ok) { if (errorEl) errorEl.textContent = data.error || 'Could not pass.'; return; }
                    poll();
                });
            }
        })();
        </script>
    @endif
@endsection
