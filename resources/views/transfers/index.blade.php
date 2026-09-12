@extends('layouts.app')

@section('title', 'Transfers')

@section('content')
    @include('partials.page-header', [
        'title' => 'Transfers',
        'subtitle' => 'Player movement across the league.',
    ])

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Total Transfers', 'value' => $totals['count'], 'accent' => 'violet'])
        @include('partials.stat-card', ['label' => 'Approved', 'value' => $totals['approved'], 'accent' => 'emerald'])
        @include('partials.stat-card', ['label' => 'Pending', 'value' => $totals['pending'], 'accent' => 'amber'])
        @include('partials.stat-card', ['label' => 'Total Spend', 'value' => number_format((float) $totals['spend'], 0), 'accent' => 'sky'])
    </div>

    <form method="GET" action="{{ route('transfers.index') }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-800 bg-slate-900/60 p-4">
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Type</label>
            <select name="type" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                <option value="">All types</option>
                @foreach (['auction', 'direct', 'trade', 'release'] as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Status</label>
            <select name="status" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                <option value="">All statuses</option>
                @foreach (['pending', 'approved', 'rejected', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400">Filter</button>
        <a href="{{ route('transfers.index') }}" class="rounded-md border border-slate-700 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Reset</a>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Player</th>
                        <th class="px-5 py-3">From</th>
                        <th class="px-5 py-3">To</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3 text-right">Fee</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($transfers as $transfer)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-5 py-3">
                                @if ($transfer->player)
                                    <a href="{{ route('players.show', $transfer->player) }}" class="font-medium text-white hover:text-emerald-400">
                                        {{ $transfer->player->name }}
                                    </a>
                                @else
                                    <span class="text-slate-500">Unknown</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-400">{{ $transfer->fromTeam?->name ?? 'Free Agent' }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $transfer->toTeam?->name ?? 'Released' }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ ucfirst($transfer->type) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-emerald-400">{{ number_format((float) $transfer->fee, 0) }}</td>
                            <td class="px-5 py-3">@include('partials.status-badge', ['status' => $transfer->status])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">No transfers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $transfers->links() }}
    </div>
@endsection
