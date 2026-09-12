@extends('layouts.app')

@section('title', 'Transfers')

@section('content')
    @include('partials.page-header', [
        'title' => 'Transfers',
        'eyebrow' => 'Player Movement',
        'subtitle' => 'Every transfer recorded across the league.',
    ])

    <div class="grid grid-cols-2 gap-4 mb-10 sm:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Total Transfers', 'value' => $totals['count']])
        @include('partials.stat-card', ['label' => 'Approved', 'value' => $totals['approved'], 'accent' => 'up'])
        @include('partials.stat-card', ['label' => 'Pending', 'value' => $totals['pending'], 'accent' => 'gold'])
        @include('partials.stat-card', ['label' => 'Total Spend', 'value' => (float) $totals['spend'], 'accent' => 'gold', 'money' => true])
    </div>

    <div class="card-section mb-10 p-6">
        <form method="GET" action="{{ route('transfers.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="eyebrow block mb-2">Type</label>
                <select name="type" class="field px-3 py-2 text-sm">
                    <option value="">All Types</option>
                    @foreach (['auction', 'direct', 'trade', 'release'] as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="eyebrow block mb-2">Status</label>
                <select name="status" class="field px-3 py-2 text-sm">
                    <option value="">All Statuses</option>
                    @foreach (['pending', 'approved', 'rejected', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-accent px-5 py-2.5">Filter</button>
            <a href="{{ route('transfers.index') }}" class="btn-ghost px-5 py-2.5">Reset</a>
        </form>
    </div>

    <div class="card-section overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Player</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Type</th>
                    <th style="text-align:right;">Fee</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transfers as $transfer)
                    <tr>
                        <td>
                            @if ($transfer->player)
                                <a href="{{ route('players.show', $transfer->player) }}" class="text-link font-medium">{{ $transfer->player->name }}</a>
                            @else
                                <span style="color: var(--paper-faint);">Unknown</span>
                            @endif
                        </td>
                        <td style="color: var(--paper-dim);">{{ $transfer->fromTeam?->name ?? 'Free Agent' }}</td>
                        <td style="color: var(--paper-dim);">{{ $transfer->toTeam?->name ?? 'Released' }}</td>
                        <td style="color: var(--paper-dim);">{{ ucfirst($transfer->type) }}</td>
                        <td style="text-align:right; color: var(--gold); font-weight: 600;"><x-money :amount="$transfer->fee" /></td>
                        <td>@include('partials.status-badge', ['status' => $transfer->status])</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No transfers found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-10">
        {{ $transfers->links() }}
    </div>
@endsection
