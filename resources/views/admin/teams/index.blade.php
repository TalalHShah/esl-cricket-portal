@extends('layouts.admin')

@section('title', 'Manage Teams')

@section('content')
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-4xl font-black text-white">Teams</h1>
            <p class="mt-2 text-slate-400">{{ $teams->total() }} teams in the league</p>
        </div>
        <div class="flex items-center gap-3">
            @include('partials.sort-bar', ['current' => $sort, 'sortId' => 'teams', 'options' => [
                'name_asc' => 'Name — A to Z',
                'name_desc' => 'Name — Z to A',
                'budget_desc' => 'Budget — High to Low',
                'budget_asc' => 'Budget — Low to High',
                'spent_desc' => 'Spent — High to Low',
                'remaining_desc' => 'Remaining — High to Low',
            ]])
            <a href="{{ route('admin.teams.create') }}" class="px-5 py-3 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold transition whitespace-nowrap">
                + Add Team
            </a>
        </div>
    </div>

    {{-- Teams Grid --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @forelse ($teams as $team)
            <div class="cricket-card rounded-2xl p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full" style="background-color: {{ $team->primary_color }}"></div>
                        <div>
                            <h3 class="font-bold text-white text-lg">{{ $team->name }}</h3>
                            <p class="text-xs text-slate-400">Manager: {{ $team->manager?->name ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="space-x-2">
                        <a href="{{ route('admin.teams.edit', $team) }}" class="px-3 py-1 rounded text-sm font-bold bg-amber-600/20 text-amber-300 hover:bg-amber-600/30 transition inline-block">
                            Edit
                        </a>
                        <form action="{{ route('admin.teams.destroy', $team) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this team?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1 rounded text-sm font-bold bg-red-600/20 text-red-300 hover:bg-red-600/30 transition">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-400">Budget:</span>
                        <span class="text-sm font-bold text-emerald-300"><x-money :amount="$team->budget" :size="14" /></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-400">Players:</span>
                        <span class="text-sm font-bold text-blue-300">{{ $team->players_count }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full cricket-card rounded-2xl p-12 text-center">
                <p class="text-slate-500">No teams found</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-8">
        {{ $teams->links() }}
    </div>
@endsection
