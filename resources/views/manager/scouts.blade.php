@extends('layouts.manager')

@section('title', 'Scouts')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">Player Recruitment</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Scout Players</h1>
        <p class="text-sm mt-2" style="color: var(--paper-faint);">{{ $players->total() }} player{{ $players->total() !== 1 ? 's' : '' }} across the league — everyone outside your own squad</p>
    </div>

    <div class="card-section mb-10 p-6">
        <form method="GET" action="{{ route('manager.scouts') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="eyebrow block mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Player name" class="field px-3 py-2 text-sm">
            </div>
            <div>
                <label class="eyebrow block mb-2">Role</label>
                <select name="role" class="field px-3 py-2 text-sm">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="eyebrow block mb-2">Tier</label>
                <select name="tier" class="field px-3 py-2 text-sm">
                    <option value="">All Tiers</option>
                    @foreach ($tiers as $tier)
                        <option value="{{ $tier }}" @selected(request('tier') === $tier)>{{ $tier }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="eyebrow block mb-2">Availability</label>
                <select name="availability" class="field px-3 py-2 text-sm">
                    <option value="">All Players</option>
                    <option value="free" @selected(request('availability') === 'free')>Free Agents Only</option>
                    <option value="signed" @selected(request('availability') === 'signed')>Signed Players Only</option>
                </select>
            </div>
            <button type="submit" class="btn-accent px-5 py-2.5">Search</button>
            <a href="{{ route('manager.scouts') }}" class="btn-ghost px-5 py-2.5">Reset</a>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($players as $player)
            @php
                $team = $player->team;
                $isFree = is_null($team);
                $primary = $team->primary_color ?? '#1D4ED8';
                $secondary = $team->secondary_color ?? '#0D1220';
            @endphp

            @if($isFree)
                {{-- Free Agent Card --}}
                <div class="card-section lift-on-hover p-4" style="border-color: var(--gold);">
                    <div class="flex items-center justify-between mb-3">
                        <span class="tag gold">Free Agent</span>
                        <span class="tag">{{ $player->tier }}</span>
                    </div>
                    <div class="player-portrait mb-4" style="aspect-ratio: 3/4;">
                        @if($player->image)
                            <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                        @else
                            <div class="initials">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                        @endif
                    </div>
                    <h3 class="text-base font-semibold mb-1" style="color: var(--paper);">{{ $player->name }}</h3>
                    <p class="text-xs mb-1" style="color: var(--paper-faint);">{{ $player->role }} — {{ $player->country }}</p>
                    @if($player->age)
                        <p class="text-xs mb-3" style="color: var(--paper-faint);">Age {{ $player->age }}</p>
                    @endif
                    <p class="text-lg font-semibold mb-4" style="color: var(--gold);">{{ number_format((float) $player->current_value, 0) }}</p>

                    @if(auth()->user()->managedTeam)
                        <form method="POST" action="{{ route('manager.scouts.sign', $player) }}">
                            @csrf
                            <button type="submit" class="btn-accent w-full py-2 text-xs">Sign Player</button>
                        </form>
                    @endif
                </div>
            @else
                {{-- Signed Player Card — team-themed --}}
                <div class="lift-on-hover relative overflow-hidden p-4"
                     style="border-radius: 2px; border: 1px solid var(--line-strong);
                            background-image: linear-gradient(165deg, rgba(4,6,14,0.28) 0%, rgba(4,6,14,0.86) 100%), linear-gradient(160deg, {{ $primary }} 0%, {{ $secondary }} 100%);">

                    @if($team->logo)
                        <img src="{{ asset('storage/' . $team->logo) }}" alt=""
                             style="position:absolute; top:-10%; right:-15%; width:75%; height:auto; opacity:0.22; pointer-events:none; filter: grayscale(20%);">
                    @endif

                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <span class="tag" style="border-color: rgba(255,255,255,0.5); color: #fff; background: rgba(0,0,0,0.25);">{{ $team->short_name ?? $team->name }}</span>
                            <span class="tag" style="border-color: rgba(255,255,255,0.5); color: #fff; background: rgba(0,0,0,0.25);">{{ $player->tier }}</span>
                        </div>

                        <div class="player-portrait mb-4" style="aspect-ratio: 3/4; border-color: rgba(255,255,255,0.35); background-color: rgba(0,0,0,0.25);">
                            @if($player->image)
                                <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                            @else
                                <div class="initials" style="color: rgba(255,255,255,0.7);">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                            @endif
                        </div>

                        <h3 class="text-base font-semibold mb-1" style="color: #fff;">{{ $player->name }}</h3>
                        <p class="text-xs mb-3" style="color: rgba(255,255,255,0.75);">{{ $player->role }} — {{ $team->name }}</p>
                        <p class="text-lg font-semibold mb-4" style="color: #fff;">{{ number_format((float) $player->current_value, 0) }}</p>

                        @if(auth()->user()->managedTeam)
                            <a href="{{ route('manager.transfers', ['search' => $player->name]) }}"
                               class="block w-full text-center py-2 text-xs font-semibold uppercase tracking-wide"
                               style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.4); border-radius: 2px;">
                                Make Offer
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        @empty
            <div class="sm:col-span-2 lg:col-span-4">
                <div class="card-section p-12 text-center" style="color: var(--paper-faint);">No players match your filters</div>
            </div>
        @endforelse
    </div>

    <div class="mt-10">
        {{ $players->links() }}
    </div>
@endsection
