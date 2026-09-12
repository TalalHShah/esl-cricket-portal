@extends('layouts.manager')

@section('title', 'Transfer Market')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white mb-2 flex items-center gap-2"><span>🔄</span> Transfer Market</h1>
        <p class="text-lg text-slate-400">Make offers for players currently signed with other teams</p>
    </div>

    @if($team)
        <div class="stat-box mb-8 inline-block">
            <div class="stat-value">{{ number_format($team->remainingBudget(), 0) }}</div>
            <div class="stat-label">Available Budget</div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Listed Players --}}
        <div class="lg:col-span-2">
            <div class="card-section rounded mb-6 p-6">
                <form method="GET" action="{{ route('manager.transfers') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Player name..."
                               class="rounded px-3 py-2 text-sm text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Role</label>
                        <select name="role" class="rounded px-3 py-2 text-sm text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                            <option value="">All Roles</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-accent px-4 py-2 text-sm font-bold rounded">Search</button>
                </form>
            </div>

            <div class="space-y-4">
                @forelse ($listedPlayers as $player)
                    <div class="card-section rounded p-6">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-black text-white">{{ $player->name }}</h3>
                                <p class="text-xs text-slate-400 mb-2">{{ $player->role }} • {{ $player->tier }} • {{ $player->team?->name }}</p>
                                <p class="stat-value text-base">{{ number_format((float) $player->current_value, 0) }}</p>
                            </div>
                            @if($team)
                                <form method="POST" action="{{ route('manager.transfers.offer', $player) }}" class="flex items-center gap-2">
                                    @csrf
                                    <input type="number" name="fee" required min="1" placeholder="Offer amount"
                                           class="w-36 rounded px-3 py-2 text-sm text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                                    <button type="submit" class="btn-accent px-4 py-2 text-sm font-bold rounded whitespace-nowrap">Make Offer</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="card-section rounded p-12 text-center text-slate-500">No players available in the market</div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $listedPlayers->links() }}
            </div>
        </div>

        {{-- My Transfer Activity --}}
        <div>
            <div class="card-section rounded">
                <div class="card-header flex items-center gap-2">
                    <span>📋</span>
                    <h2>My Activity</h2>
                </div>
                <div class="divide-y" style="border-color: var(--border);">
                    @forelse ($myTransfers as $transfer)
                        <div class="p-5">
                            <p class="font-bold text-white text-sm">{{ $transfer->player?->name }}</p>
                            <p class="text-xs text-slate-400 mb-2">
                                {{ $transfer->fromTeam?->short_name ?? '—' }} → {{ $transfer->toTeam?->short_name ?? '—' }}
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="stat-value text-sm">{{ number_format((float) $transfer->fee, 0) }}</span>
                                <span class="px-2 py-1 text-xs font-bold rounded"
                                      style="background-color: {{ $transfer->status === 'approved' ? 'var(--success)' : ($transfer->status === 'rejected' ? '#DC2626' : 'var(--accent)') }}; color: {{ $transfer->status === 'pending' ? 'var(--primary)' : 'white' }};">
                                    {{ ucfirst($transfer->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-sm">No transfer activity yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
