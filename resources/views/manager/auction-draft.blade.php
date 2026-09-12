@extends('layouts.auction-room')

@section('title', 'Country Draft')

@section('head')
    <style>
        .seat-avatar { position: relative; width: 64px; height: 64px; border-radius: 9999px; overflow: hidden; border: 2px solid var(--line-strong); background-color: var(--surface-raised); flex-shrink: 0; }
        .seat-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .seat-crest { position: absolute; bottom: -4px; right: -4px; width: 22px; height: 22px; border-radius: 9999px; border: 2px solid var(--surface); background-color: var(--surface-raised); overflow: hidden; }
        .seat-crest img { width: 100%; height: 100%; object-fit: cover; }
        .turn-card { text-align: center; opacity: 0.45; transition: opacity 200ms, transform 200ms; }
        .turn-card.is-active { opacity: 1; transform: translateY(-4px); }
        .turn-card .role-tag { font-size: 0.6rem; margin-top: 0.35rem; }
        @keyframes spinCycle { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
        .spinning { animation: spinCycle 120ms linear infinite; }
        .player-pick-row { display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border-bottom: 1px solid var(--line); }
        .player-pick-row:last-child { border-bottom: none; }
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
        <div class="mb-8">
            <p class="eyebrow gold mb-2">Country Draft</p>
            <h1 class="font-display text-4xl md:text-5xl font-semibold mb-2" style="color: var(--paper);" id="countryHeading">
                {{ $draft->current_country ?? 'Awaiting Next Country' }}
            </h1>
            <p class="text-sm" style="color: var(--paper-faint);" id="statusLine">&nbsp;</p>
        </div>

        <div class="flex items-start justify-center gap-6 mb-10 flex-wrap" id="turnStrip"></div>

        <div class="mb-6" id="burnedRow"></div>

        <div id="mainPanel"></div>
    @endif

    @if ($draft)
        <script>
        (function () {
            const draftId = {{ $draft->id }};
            const stateUrl = @json(route('manager.draft.state', $draft));
            const spinUrl = @json(route('manager.draft.spin', $draft));
            const nominateUrl = @json(route('manager.draft.nominate', $draft));
            const skipUrl = @json(route('manager.draft.skip', $draft));
            const roomUrlTemplate = @json(route('manager.auction.room', ['auctionSession' => '__ID__']));
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            let redirecting = false;
            let spinning = false;

            function post(url, body) {
                return fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(Object.assign({ _token: csrf }, body || {})).toString(),
                }).then(async r => ({ ok: r.ok, data: await r.json() }));
            }

            function fmtMoney(n) {
                return `<span class="inline-flex items-center gap-1.5"><svg width="14" height="14" viewBox="0 0 32 32" style="display:inline-block; vertical-align:-2px;"><circle cx="16" cy="16" r="15" fill="var(--gold)" stroke="var(--gold-dim)" stroke-width="1.5"/><circle cx="16" cy="16" r="10.5" fill="none" stroke="var(--gold-dim)" stroke-width="1.25"/><text x="16" y="21" font-family="Georgia, serif" font-size="14" font-weight="700" text-anchor="middle" fill="var(--gold-dim)">E</text></svg><span>${Math.round(n).toLocaleString('en-US')}</span></span>`;
            }

            function renderTeamCard(team, roleLabel, isActive) {
                if (!team) return '';
                return `
                    <div class="turn-card ${isActive ? 'is-active' : ''}">
                        <div class="seat-avatar" style="margin: 0 auto;">
                            ${team.manager_avatar ? `<img src="${team.manager_avatar}" alt="">` : `<div class="initials">${(team.manager_name || '?').substring(0,2).toUpperCase()}</div>`}
                            ${team.team_logo ? `<span class="seat-crest"><img src="${team.team_logo}" alt=""></span>` : ''}
                        </div>
                        <p class="text-xs font-semibold mt-2" style="color: var(--paper);">${team.manager_name || ''}</p>
                        <p class="text-xs" style="color: var(--paper-faint);">${team.team_short_name || ''}</p>
                        ${roleLabel ? `<span class="tag gold role-tag">${roleLabel}</span>` : ''}
                    </div>
                `;
            }

            function renderTurnStrip(data) {
                const strip = document.getElementById('turnStrip');
                if (!strip) return;
                strip.innerHTML = data.turn_order.map(team => {
                    const roles = [];
                    if (data.country_picker && team.team_id === data.country_picker.team_id) roles.push('Spins Next Country');
                    if (data.active_picker && team.team_id === data.active_picker.team_id) roles.push('Picking Now');
                    const isActive = roles.length > 0;
                    return renderTeamCard(team, roles.join(' / '), isActive);
                }).join('');
            }

            function renderBurned(countries) {
                const row = document.getElementById('burnedRow');
                if (!row) return;
                if (!countries.length) { row.innerHTML = ''; return; }
                row.innerHTML = '<p class="eyebrow mb-2" style="color: var(--paper-faint);">Burned Countries</p><div class="flex flex-wrap gap-2">' +
                    countries.map(c => `<span class="tag" style="opacity:0.6;">${c}</span>`).join('') + '</div>';
            }

            function renderPlayerList(players, isYourTurn) {
                if (!players.length) {
                    return '<p class="text-sm" style="color: var(--paper-faint);">No available players left from this country.</p>';
                }
                return players.map(p => `
                    <div class="player-pick-row">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--paper);">${p.name}</p>
                            <p class="text-xs" style="color: var(--paper-faint);">${p.role} — ${p.tier}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-semibold" style="color: var(--gold);">${fmtMoney(p.base_value)}</span>
                            ${isYourTurn ? `<button type="button" class="btn-accent px-4 py-2 text-xs nominate-btn" data-id="${p.id}">Nominate</button>` : ''}
                        </div>
                    </div>
                `).join('');
            }

            function renderMain(data) {
                const panel = document.getElementById('mainPanel');
                const statusLine = document.getElementById('statusLine');
                const heading = document.getElementById('countryHeading');
                if (!panel) return;

                heading.textContent = data.current_country || 'Awaiting Next Country';

                if (data.status === 'completed') {
                    statusLine.textContent = 'Every country has been drafted.';
                    panel.innerHTML = `<div class="card-section p-12 text-center"><p class="eyebrow gold mb-3">Draft Complete</p><p class="text-sm" style="color: var(--paper-faint);">All available players have been auctioned. Head to Auctions to review results.</p></div>`;
                    return;
                }

                if (!data.current_country) {
                    if (data.is_your_turn_to_spin) {
                        statusLine.textContent = 'Spin to reveal the next country.';
                        panel.innerHTML = `<div class="card-section p-12 text-center"><button type="button" id="spinBtn" class="btn-accent px-10 py-4 text-lg">Spin For Country</button></div>`;
                        const btn = document.getElementById('spinBtn');
                        if (btn) btn.addEventListener('click', doSpin);
                    } else {
                        statusLine.textContent = `Waiting for ${data.country_picker ? data.country_picker.manager_name : 'the next manager'} to spin.`;
                        panel.innerHTML = `<div class="card-section p-12 text-center" style="color: var(--paper-faint);">Sit tight — the floor will open once the country is revealed.</div>`;
                    }
                    return;
                }

                if (data.active_session_id) {
                    statusLine.textContent = 'A player is up for bidding — taking you there now...';
                    panel.innerHTML = `<div class="card-section p-12 text-center" style="color: var(--paper-faint);">Redirecting to the bid room…</div>`;
                    if (!redirecting) {
                        redirecting = true;
                        window.location.href = roomUrlTemplate.replace('__ID__', data.active_session_id);
                    }
                    return;
                }

                if (data.is_your_turn_to_pick) {
                    statusLine.textContent = `Your turn — nominate a player from ${data.current_country}, or skip.`;
                    panel.innerHTML = `
                        <div class="card-section mb-4">
                            ${renderPlayerList(data.available_players, true)}
                        </div>
                        <div class="text-center">
                            <button type="button" id="skipBtn" class="btn-ghost px-8 py-3">Skip My Turn</button>
                        </div>
                    `;
                    document.querySelectorAll('.nominate-btn').forEach(b => b.addEventListener('click', () => doNominate(b.dataset.id)));
                    const skipBtn = document.getElementById('skipBtn');
                    if (skipBtn) skipBtn.addEventListener('click', doSkip);
                } else {
                    statusLine.textContent = `Waiting for ${data.active_picker ? data.active_picker.manager_name : 'the next manager'} to pick from ${data.current_country}.`;
                    panel.innerHTML = `<div class="card-section">${renderPlayerList(data.available_players, false)}</div>`;
                }
            }

            function applyState(data) {
                renderTurnStrip(data);
                renderBurned(data.burned_countries || []);
                if (!spinning) renderMain(data);
            }

            function poll() {
                fetch(stateUrl, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(applyState)
                    .catch(() => {});
            }
            poll();
            setInterval(poll, 2000);

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
                        if (!ok) {
                            alert(data.error || 'Could not spin.');
                        }
                        poll();
                    }, 1100);
                });
            }

            function doNominate(playerId) {
                post(nominateUrl, { player_id: playerId }).then(({ ok, data }) => {
                    if (!ok) { alert(data.error || 'Could not nominate.'); return; }
                    poll();
                });
            }

            function doSkip() {
                post(skipUrl).then(({ ok, data }) => {
                    if (!ok) { alert(data.error || 'Could not skip.'); return; }
                    poll();
                });
            }
        })();
        </script>
    @endif
@endsection
