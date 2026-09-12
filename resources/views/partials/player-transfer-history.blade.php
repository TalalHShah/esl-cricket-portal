@props(['transfers'])

<div class="mt-10">
    <p class="eyebrow gold mb-4">Career Journey</p>
    <div class="card-section p-6">
        @forelse ($transfers as $transfer)
            <div class="flex gap-4 {{ ! $loop->last ? 'pb-5 mb-5' : '' }}" style="{{ ! $loop->last ? 'border-bottom: var(--rule);' : '' }}">
                <div class="flex flex-col items-center flex-shrink-0" style="width: 12px;">
                    <span style="width: 10px; height: 10px; border-radius: 9999px; background-color: {{ $transfer->status === 'approved' ? 'var(--gold)' : ($transfer->status === 'rejected' ? 'var(--live)' : 'var(--paper-faint)') }}; margin-top: 4px;"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between flex-wrap gap-2 mb-1">
                        <p class="text-sm font-semibold" style="color: var(--paper);">
                            @if ($transfer->status !== 'approved')
                                <span class="tag {{ $transfer->status === 'rejected' ? '' : 'gold' }}" style="font-size: 0.6rem; margin-right: 0.5rem;">{{ ucfirst($transfer->status) }}</span>
                            @endif
                            @if ($transfer->type === 'auction')
                                Auctioned to {{ $transfer->toTeam?->name ?? '—' }}
                            @elseif (is_null($transfer->from_team_id))
                                Signed by {{ $transfer->toTeam?->name ?? '—' }}
                            @elseif ($transfer->type === 'release')
                                Released by {{ $transfer->fromTeam?->name ?? '—' }}
                            @else
                                Transferred: {{ $transfer->fromTeam?->name ?? 'Free Agent' }} &rarr; {{ $transfer->toTeam?->name ?? '—' }}
                            @endif
                        </p>
                        <span class="text-xs whitespace-nowrap" style="color: var(--paper-faint);">
                            {{ optional($transfer->effective_at ?? $transfer->created_at)->format('d M Y') }}
                        </span>
                    </div>
                    <p class="text-sm" style="color: var(--paper-dim);">
                        @if ($transfer->fee > 0)
                            Fee — <x-money :amount="$transfer->fee" :size="13" />
                        @else
                            No fee recorded
                        @endif
                        <span style="color: var(--paper-faint);">&nbsp;—&nbsp;{{ ucfirst($transfer->type) }}</span>
                    </p>
                    @if ($transfer->notes)
                        <p class="text-xs mt-1" style="color: var(--paper-faint);">{{ $transfer->notes }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-center py-6" style="color: var(--paper-faint);">No transfer history yet — this player has always been a free agent.</p>
        @endforelse
    </div>
</div>
