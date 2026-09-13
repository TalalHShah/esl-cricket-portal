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

        .countdown-ring { width: 72px; height: 72px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; border: 4px solid var(--line-strong); font-family: 'Barlow Condensed', sans-serif; font-weight: 700; font-size: 1.4rem; color: var(--paper); transition: border-color 200ms, color 200ms; margin: 0 auto; }
        .countdown-ring.urgent { border-color: var(--live); color: var(--live); }

        /* ---------- Auction pool (queued for the later Auction phase) ---------- */
        .pool-tier-heading { display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem; }
        .pool-tier-dot { width: 8px; height: 8px; border-radius: 9999px; flex-shrink: 0; }
        .pool-tier-dot.tier-platinum { background: #B9A9D0; }
        .pool-tier-dot.tier-diamond { background: #7FD8E8; }
        .pool-tier-dot.tier-gold { background: var(--gold); }
        .pool-tier-dot.tier-silver { background: #B8C0CC; }
        .pool-row { display: flex; align-items: center; justify-content: space-between; padding: 0.4rem 0; border-bottom: 1px solid var(--line); font-size: 0.75rem; }
        .pool-row:last-child { border-bottom: none; }
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
                <p class="eyebrow gold mb-2">Country Draft — Player Selection</p>
                <h1 class="font-display text-4xl md:text-5xl font-semibold mb-2" style="color: var(--paper);" id="countryHeading">
                    {{ $draft->current_country ?? 'Awaiting Next Country' }}
                </h1>
                <p class="text-sm" style="color: var(--paper-faint);" id="statusLine">&nbsp;</p>
            </div>
            <div class="text-center">
                <div id="timerSlot"></div>
                @if (auth()->user()->isAdmin())
                    <div class="flex flex-col gap-2 mt-2">
                        @if (in_array($draft->status, ['active', 'bonus_round'], true))
                            <form method="POST" action="{{ route('admin.draft.end', $draft) }}" onsubmit="return confirm('End the draft now? Anything already queued stays available for the Auction phase.');">
                                @csrf
                                <button type="submit" class="btn-ghost px-4 py-2 text-xs" style="color: var(--live); border-color: var(--live);">End Draft</button>
                            </form>
                        @elseif ($draft->status === 'completed')
                            <form method="POST" action="{{ route('admin.draft.bonus-round', $draft) }}">
                                @csrf
                                <button type="submit" class="btn-accent px-4 py-2 text-xs">Start Bonus Round</button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="card-section p-4 mb-6">
            <p class="text-xs" style="color: var(--paper-faint);">
                <strong style="color: var(--paper-dim);">How this works:</strong>
                this is drafting, not auctioning — nobody bids or spends money here. The manager marked <span class="tag gold" style="font-size:0.6rem;">Spins Next</span> spins a random country; the manager after them in order gets first pick from it. Picking rotates through everyone in order — pick a player to queue them for the auction, or pass. <strong style="color: var(--paper-dim);">Passing is permanent for this country</strong> — you're out of the rotation until it's retired, though you can jump back in any time before that. Every turn has a {{ $turnTimeoutSeconds }}-second clock; if it runs out, that manager is passed automatically. Once everyone's passed or the country runs out of players, it's retired and the <em>next</em> manager in order spins for the next country. Once the draft is done, the admin runs the actual Auction — a separate event, worked through Platinum, Diamond, Gold, then Silver — where every manager can bid on any queued player.
            </p>
        </div>

        <div class="draft-grid">
            <div class="draft-stage" id="mainPanel"></div>

            <aside class="draft-sidebar">
                <div class="sidebar-tabs">
                    <button type="button" class="sidebar-tab is-active" data-sidebar-tab="order">Turn Order</button>
                    <button type="button" class="sidebar-tab" data-sidebar-tab="pool">Auction Pool <span id="poolCountBadge"></span></button>
                    <button type="button" class="sidebar-tab" data-sidebar-tab="shortlist">Shortlist</button>
                    <button type="button" class="sidebar-tab" data-sidebar-tab="call">Live Call</button>
                </div>

                <div class="sidebar-panel" id="sidebarOrder">
                    <p class="eyebrow mb-2" style="color: var(--paper-faint);">Draft Order</p>
                    <div id="turnStrip" class="flex flex-col gap-2"></div>
                    <div id="burnedRow" class="mt-4"></div>
                </div>

                <div class="sidebar-panel hidden" id="sidebarPool">
                    <p class="eyebrow mb-2" style="color: var(--paper-faint);">Queued For Auction</p>
                    <p class="text-xs mb-3" style="color: var(--paper-faint);">Players picked so far, grouped by category. The admin runs the actual auction separately, working through Platinum first.</p>
                    <div id="poolList"></div>
                </div>

                <div class="sidebar-panel hidden" id="sidebarShortlist">
                    <p class="eyebrow mb-2" style="color: var(--paper-faint);">Your Shortlist</p>
                    <p class="text-xs mb-3" style="color: var(--paper-faint);">Players you've starred from <a href="{{ route('manager.scouts') }}" class="underline">Scouts</a> — the ones from the country currently in play are highlighted.</p>
                    <div id="shortlistList" class="flex flex-col gap-2"></div>
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
            const rejoinUrl = @json(route('manager.draft.rejoin', $draft));
            const bonusNominateUrl = @json(route('manager.draft.bonus-nominate', $draft));
            const callUrl = @json(route('manager.call', 'draft-' . $draft->id));
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            let spinning = false;
            let deadline = null;

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

            // ---------- Sidebar tabs (Turn Order / Auction Pool / Shortlist / Live Call) ----------
            document.querySelectorAll('.sidebar-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('is-active'));
                    tab.classList.add('is-active');
                    document.getElementById('sidebarOrder').classList.toggle('hidden', tab.dataset.sidebarTab !== 'order');
                    document.getElementById('sidebarPool').classList.toggle('hidden', tab.dataset.sidebarTab !== 'pool');
                    document.getElementById('sidebarShortlist').classList.toggle('hidden', tab.dataset.sidebarTab !== 'shortlist');
                    document.getElementById('sidebarCall').classList.toggle('hidden', tab.dataset.sidebarTab !== 'call');
                });
            });

            const TIER_ORDER = ['Platinum', 'Diamond', 'Gold', 'Silver'];

            function renderPool(pool) {
                const badge = document.getElementById('poolCountBadge');
                if (badge) badge.textContent = pool.length ? `(${pool.length})` : '';

                const el = document.getElementById('poolList');
                if (!el) return;
                if (!pool.length) {
                    el.innerHTML = '<p class="text-xs" style="color: var(--paper-faint);">No players queued yet — picks made during the draft show up here.</p>';
                    return;
                }

                el.innerHTML = TIER_ORDER.map(tier => {
                    const players = pool.filter(p => p.tier === tier);
                    if (!players.length) return '';
                    return `
                        <div class="mb-4">
                            <div class="pool-tier-heading">
                                <span class="pool-tier-dot tier-${tier.toLowerCase()}"></span>
                                <p class="eyebrow" style="color: var(--paper-dim);">${tier} (${players.length})</p>
                            </div>
                            ${players.map(p => `
                                <div class="pool-row">
                                    <span style="color: var(--paper);">${p.player_name}</span>
                                    <span style="color: var(--paper-faint);">${fmtMoney(p.base_value, 12)}</span>
                                </div>
                            `).join('')}
                        </div>
                    `;
                }).join('');
            }

            function renderShortlist(shortlist) {
                const el = document.getElementById('shortlistList');
                if (!el) return;
                if (!shortlist || !shortlist.length) {
                    el.innerHTML = '<p class="text-xs" style="color: var(--paper-faint);">No shortlisted players yet — star players on the Scouts page.</p>';
                    return;
                }
                el.innerHTML = shortlist.map(p => `
                    <div class="card-section p-3" style="${p.is_current_country ? 'border-left: 3px solid var(--gold);' : ''}">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold" style="color: var(--paper);">${p.name}</p>
                            ${p.is_current_country ? '<span class="tag gold" style="font-size:0.55rem;">In Play</span>' : ''}
                        </div>
                        <p class="text-xs" style="color: var(--paper-faint);">${p.role} — ${p.country}</p>
                        <p class="text-xs font-semibold mt-1" style="color: var(--gold);">${fmtMoney(p.base_value, 13)}</p>
                    </div>
                `).join('');
            }

            // ---------- Turn strip ----------
            function renderTeamCard(team, roleLabel, isActive, hasPassedCountry) {
                if (!team) return '';
                return `
                    <div class="turn-card ${isActive ? 'is-active' : ''} ${hasPassedCountry ? 'is-away' : ''}">
                        <div class="seat-avatar">
                            ${team.manager_avatar ? `<img src="${team.manager_avatar}" alt="">` : `<div class="initials">${(team.manager_name || '?').substring(0,2).toUpperCase()}</div>`}
                            ${team.team_logo ? `<span class="seat-crest"><img src="${team.team_logo}" alt=""></span>` : ''}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold truncate" style="color: var(--paper);">${team.manager_name || ''}</p>
                            <p class="text-xs truncate" style="color: var(--paper-faint);">${team.team_short_name || ''}</p>
                            <div class="flex flex-wrap gap-1 mt-1">
                                ${roleLabel ? `<span class="tag gold role-tag">${roleLabel}</span>` : ''}
                                ${hasPassedCountry ? '<span class="tag passed role-tag">Out — Passed</span>' : ''}
                            </div>
                        </div>
                    </div>
                `;
            }

            function renderTurnStrip(data) {
                const strip = document.getElementById('turnStrip');
                if (!strip) return;
                const passedIds = data.passed_team_ids || [];
                strip.innerHTML = data.turn_order.map(team => {
                    const roles = [];
                    if (data.country_picker && team.team_id === data.country_picker.team_id) roles.push('Spins Next');
                    if (data.active_picker && team.team_id === data.active_picker.team_id) roles.push('Picking Now');
                    return renderTeamCard(team, roles.join(' / '), roles.length > 0, passedIds.includes(team.team_id));
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
                    <div class="player-card is-tiltable" style="${p.is_shortlisted ? 'border-color: var(--gold-dim);' : ''}">
                        <div class="player-card-portrait" style="position:relative;">
                            ${p.image ? `<img src="${p.image}" alt="">` : `<div class="initials">${(p.name || '?').substring(0,2).toUpperCase()}</div>`}
                            ${p.is_shortlisted ? `<span style="position:absolute; top:6px; right:6px;" title="On your shortlist"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--gold)" stroke="var(--gold)" stroke-width="1.5"><polygon points="12 2 15.09 8.63 22 9.24 16.5 14.14 18.18 21 12 17.27 5.82 21 7.5 14.14 2 9.24 8.91 8.63 12 2"/></svg></span>` : ''}
                        </div>
                        <p class="player-card-name">${p.name}</p>
                        <p class="player-card-meta">${p.role} — ${p.tier}</p>
                        <p class="player-card-value">${fmtMoney(p.base_value, 16)}</p>
                        ${isYourTurn ? `<button type="button" class="btn-accent py-2 text-xs nominate-btn" data-id="${p.id}">Pick For Auction</button>` : ''}
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

            function setStatusLine(html, urgent) {
                const el = document.getElementById('statusLine');
                if (el) el.innerHTML = html;
                if (window.setLiveStatus) window.setLiveStatus(html, !!urgent || /your turn/i.test(html));
            }

            function renderTurnTimer(data, label) {
                const slot = document.getElementById('timerSlot');
                if (!slot) return;
                if (!data.turn_deadline_at) { slot.innerHTML = ''; deadline = null; return; }
                deadline = data.turn_deadline_at;
                slot.innerHTML = `<div class="countdown-ring" id="countdownRing">—</div><p class="text-xs mt-1" style="color: var(--paper-faint);">${label}</p>`;
                tickCountdown();
            }

            function passedBanner(data) {
                if (!data.you_have_passed) return '';
                return `
                    <div class="card-section p-4 mb-4" style="border-left: 3px solid var(--live);">
                        <p class="text-sm font-semibold mb-2" style="color: var(--paper);">You've passed on ${data.current_country} — you're out of the picking order for this country.</p>
                        <button type="button" id="rejoinBtn" class="btn-ghost px-6 py-2 text-xs">Jump Back In</button>
                    </div>
                `;
            }

            function renderNominatePanel(data) {
                const panel = document.getElementById('mainPanel');
                renderTurnTimer(data, 'auto-pass in');

                if (data.is_your_turn_to_pick) {
                    setStatusLine(`Your turn — nominate a player from ${data.current_country}, or pass.`, true);
                } else if (data.you_have_passed) {
                    setStatusLine(`You've passed on ${data.current_country} — jump back in any time before it's retired.`);
                } else {
                    setStatusLine(`Waiting for <strong style="color: var(--paper-dim);">${data.active_picker ? data.active_picker.manager_name : 'the next manager'}</strong> to pick from ${data.current_country}.`);
                }

                const banner = data.is_your_turn_to_pick
                    ? `<div class="card-section p-4 mb-4" style="border-left: 3px solid var(--gold);">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <p class="text-sm font-semibold" style="color: var(--paper);">Your turn — pick a player below, or pass. If time runs out, you'll be passed automatically.</p>
                            <button type="button" id="skipBtn" class="btn-ghost px-5 py-2 text-xs whitespace-nowrap" style="color: var(--live); border-color: var(--live);">Pass — I'm Out For This Country</button>
                        </div>
                       </div>`
                    : `<div class="card-section p-4 mb-4" style="color: var(--paper-faint);"><p class="text-sm">Waiting for <strong style="color: var(--paper-dim);">${data.active_picker ? data.active_picker.manager_name : 'the next manager'}</strong> to pick — you'll get a turn once they nominate or pass. (Nominate buttons only appear for the manager whose turn it is.)</p></div>`;

                panel.innerHTML = `
                    ${passedBanner(data)}
                    ${banner}
                    <div class="card-section mb-4">
                        ${renderPlayerList(data.available_players, data.is_your_turn_to_pick)}
                    </div>
                    <p id="nominateError" class="text-xs text-center mt-3" style="color: var(--live);"></p>
                `;
                document.querySelectorAll('.nominate-btn').forEach(b => b.addEventListener('click', () => doNominate(b.dataset.id)));
                attachTilt(panel);
                const skipBtn = document.getElementById('skipBtn');
                if (skipBtn) skipBtn.addEventListener('click', doSkip);
                const rejoinBtn = document.getElementById('rejoinBtn');
                if (rejoinBtn) rejoinBtn.addEventListener('click', doRejoin);
            }

            function renderSpinPanel(data) {
                const panel = document.getElementById('mainPanel');
                renderTurnTimer(data, 'auto-spin in');
                if (data.is_your_turn_to_spin) {
                    setStatusLine('Your turn — spin to reveal the next country.', true);
                    panel.innerHTML = `<div class="card-section p-12 text-center"><button type="button" id="spinBtn" class="btn-accent px-10 py-4 text-lg">Spin For Country</button></div>`;
                    document.getElementById('spinBtn').addEventListener('click', doSpin);
                } else {
                    setStatusLine(`Waiting for <strong style="color: var(--paper-dim);">${data.country_picker ? data.country_picker.manager_name : 'the next manager'}</strong> to spin for a country.`);
                    panel.innerHTML = `<div class="card-section p-12 text-center" style="color: var(--paper-faint);">Waiting for ${data.country_picker ? data.country_picker.manager_name : 'the next manager'} to spin for a country.</div>`;
                }
            }

            function renderCompletedPanel() {
                setStatusLine('The draft is complete.');
                document.getElementById('timerSlot').innerHTML = '';
                document.getElementById('mainPanel').innerHTML = `<div class="card-section p-12 text-center"><p class="eyebrow gold mb-3">Draft Complete</p><p class="text-sm" style="color: var(--paper-faint);">Picking has finished — the admin runs the Auction from here. If squads still need more players, the admin can open a Bonus Round.</p></div>`;
            }

            function renderBonusPanel(data) {
                setStatusLine('Bonus round — anyone can pick any country, any time.', true);
                document.getElementById('timerSlot').innerHTML = '';

                const panel = document.getElementById('mainPanel');
                const countries = data.bonus_countries || [];
                const selected = selectedBonusCountry && countries.includes(selectedBonusCountry) ? selectedBonusCountry : '';

                const playersHtml = selected
                    ? renderPlayerList(data.bonus_players || [], true)
                    : '<p class="text-sm" style="color: var(--paper-faint);">Pick a country above to see who\'s left.</p>';

                panel.innerHTML = `
                    <div class="card-section p-4 mb-4" style="border-left: 3px solid var(--gold);">
                        <p class="text-sm font-semibold" style="color: var(--paper);">Bonus round is open — no turns, no timer. Anyone can pick anyone from any country, as many times as needed.</p>
                    </div>
                    <div class="card-section p-4 mb-4">
                        <label class="eyebrow block mb-2" style="color: var(--paper-faint);">Country</label>
                        <select id="bonusCountrySelect" class="field px-3 py-2 text-sm w-full sm:w-64">
                            <option value="">Choose a country...</option>
                            ${countries.map(c => `<option value="${c}" ${c === selected ? 'selected' : ''}>${c}</option>`).join('')}
                        </select>
                    </div>
                    <div class="card-section mb-4">${playersHtml}</div>
                    <p id="nominateError" class="text-xs text-center mt-3" style="color: var(--live);"></p>
                `;

                document.getElementById('bonusCountrySelect').addEventListener('change', (e) => {
                    selectedBonusCountry = e.target.value || null;
                    poll();
                });
                document.querySelectorAll('.nominate-btn').forEach(b => b.addEventListener('click', () => doBonusNominate(selected, b.dataset.id)));
                attachTilt(panel);
            }

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

            // ---------- Master render ----------
            let selectedBonusCountry = null;

            function applyState(data) {
                renderTurnStrip(data);
                renderBurned(data.burned_countries || []);
                renderShortlist(data.shortlist || []);
                renderPool(data.pool || []);

                if (data.status === 'bonus_round') {
                    renderBonusPanel(data);
                    return;
                }

                if (data.status === 'completed') {
                    renderCompletedPanel();
                    return;
                }

                if (!data.current_country) {
                    renderSpinPanel(data);
                    return;
                }

                renderNominatePanel(data);
            }

            function poll() {
                const url = selectedBonusCountry ? stateUrl + '?bonus_country=' + encodeURIComponent(selectedBonusCountry) : stateUrl;
                fetch(url, { headers: { 'Accept': 'application/json' } })
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
                if (!confirm("Pass on this country? You'll be out of the picking order until it's retired or you jump back in.")) return;
                post(skipUrl).then(({ ok, data }) => {
                    if (!ok) alert(data.error || 'Could not pass.');
                    poll();
                });
            }

            function doRejoin() {
                post(rejoinUrl).then(({ ok, data }) => {
                    if (!ok) alert(data.error || 'Could not rejoin.');
                    poll();
                });
            }

            function doBonusNominate(country, playerId) {
                const errorEl = document.getElementById('nominateError');
                if (errorEl) errorEl.textContent = '';
                post(bonusNominateUrl, { country, player_id: playerId }).then(({ ok, data }) => {
                    if (!ok) { if (errorEl) errorEl.textContent = data.error || 'Could not pick that player.'; return; }
                    poll();
                });
            }
        })();
        </script>
    @endif
@endsection
