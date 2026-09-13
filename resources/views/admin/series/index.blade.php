@extends('layouts.admin')

@section('title', 'Series')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white">Series</h1>
        <p class="mt-2 text-slate-400">{{ $series->total() }} manager-run series league-wide</p>
    </div>

    <div class="cricket-card rounded-2xl overflow-x-auto">
        <table class="w-full">
            <thead class="bg-amber-500/10 border-b border-amber-500/20">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-amber-400">Name</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-amber-400">Teams</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-amber-400">Host</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-amber-400">Status</th>
                    <th class="px-6 py-4 text-right text-xs font-bold uppercase text-amber-400">Fixtures</th>
                    <th class="px-6 py-4 text-right text-xs font-bold uppercase text-amber-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse ($series as $s)
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="px-6 py-4 font-semibold text-white">{{ $s->name }}</td>
                        <td class="px-6 py-4 text-slate-400 text-sm">{{ $s->teams->pluck('name')->join(' vs ') }}</td>
                        <td class="px-6 py-4 text-slate-400 text-sm">{{ $s->createdByTeam?->name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $s->status === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">
                                {{ str_replace('_', ' ', $s->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-slate-300">{{ $s->matches()->count() }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('manager.series.show', $s) }}" class="px-3 py-1 rounded text-sm font-bold bg-amber-600/20 text-amber-300 hover:bg-amber-600/30 transition inline-block">Manage</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">No series have been created yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">{{ $series->links() }}</div>
@endsection
