@props(['player'])

@php
    $team = $player->team;
    $isFree = is_null($team);
    $primary = $team->primary_color ?? '#1D4ED8';
    $secondary = $team->secondary_color ?? '#0D1220';
@endphp

@if($isFree)
    <div {{ $attributes->merge(['class' => 'card-section lift-on-hover p-4']) }} style="border-color: var(--gold);">
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
        <p class="text-lg font-semibold mb-4" style="color: var(--gold);"><x-money :amount="$player->current_value" :size="16" /></p>

        {{ $slot }}
    </div>
@else
    <div {{ $attributes->merge(['class' => 'lift-on-hover relative overflow-hidden p-4']) }}
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
            <p class="text-lg font-semibold mb-4" style="color: #fff;"><x-money :amount="$player->current_value" :size="16" /></p>

            {{ $slot }}
        </div>
    </div>
@endif
