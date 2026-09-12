@extends('layouts.app')

@section('title', 'Matches')

@section('content')
    @include('partials.page-header', [
        'title' => 'Matches',
        'subtitle' => $matches->total() . ' match(es) recorded.',
    ])

    <form method="GET" action="{{ route('matches.index') }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-800 bg-slate-900/60 p-4">
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Status</label>
            <select name="status" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                <option value="">All statuses</option>
                @foreach (['pending_review' => 'Pending Review', 'pending_confirmation' => 'Pending Confirmation', 'confirmed' => 'Confirmed', 'disputed' => 'Disputed'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400">Filter</button>
        <a href="{{ route('matches.index') }}" class="rounded-md border border-slate-700 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Reset</a>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Home</th>
                        <th class="px-5 py-3">Away</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Winner</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($matches as $match)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-5 py-3">
                                <a href="{{ route('matches.show', $match) }}" class="font-medium text-white hover:text-emerald-400">
                                    {{ $match->homeTeam?->name ?? 'TBD' }}
                                </a>
                            </td>
                            <td class="px-5 py-3 text-slate-300">{{ $match->awayTeam?->name ?? 'TBD' }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ optional($match->match_date)->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-3 text-slate-300">{{ $match->winnerTeam?->name ?? '—' }}</td>
                            <td class="px-5 py-3">@include('partials.status-badge', ['status' => $match->status])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-500">No matches found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $matches->links() }}
    </div>
@endsection
