@props(['players'])

<div class="card-section">
    @forelse ($players as $player)
        <a href="{{ route('manager.players.show', $player) }}"
           class="row-hover flex items-center gap-3 px-4 py-2"
           style="border-bottom: 1px solid var(--line); text-decoration: none;">
            <div class="player-portrait" style="width: 28px; height: 28px; flex-shrink: 0; border-radius: 9999px;">
                @if($player->image)
                    <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                @else
                    <div class="initials" style="font-size: 0.6rem;">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                @endif
            </div>
            <span class="text-sm font-semibold truncate" style="color: var(--paper); flex: 1 1 auto; min-width: 0;">{{ $player->name }}</span>
            <span class="text-xs whitespace-nowrap hidden sm:inline" style="color: var(--paper-faint);">{{ $player->typeLabel() }}</span>
            <span class="tag whitespace-nowrap {{ $player->is_manager_player ? 'gold' : '' }}">{{ $player->is_manager_player ? 'Manager' : $player->tier }}</span>
            <span class="text-sm font-semibold whitespace-nowrap" style="color: var(--gold); min-width: 6rem; text-align: right;"><x-money :amount="$player->current_value" /></span>
        </a>
    @empty
        <div class="p-10 text-center" style="color: var(--paper-faint);">No players in this squad yet</div>
    @endforelse
</div>
