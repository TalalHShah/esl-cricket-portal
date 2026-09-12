@extends('layouts.app')

@section('title', 'Manage Managers')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-black text-white">👥 Managers</h1>
            <p class="mt-2 text-slate-400">{{ $managers->total() }} managers in the league</p>
        </div>
        <a href="{{ route('admin.managers.create') }}" class="px-5 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition">
            + Add Manager
        </a>
    </div>

    {{-- Table --}}
    <div class="cricket-card rounded-2xl overflow-x-auto">
        <table class="w-full">
            <thead class="bg-emerald-500/10 border-b border-emerald-500/20">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-emerald-400">Name</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-emerald-400">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-emerald-400">Team</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-emerald-400">Role</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-emerald-400">Status</th>
                    <th class="px-6 py-4 text-right text-xs font-bold uppercase text-emerald-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse ($managers as $manager)
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="px-6 py-4 font-semibold text-white">{{ $manager->name }}</td>
                        <td class="px-6 py-4 text-slate-400 text-sm">{{ $manager->email }}</td>
                        <td class="px-6 py-4 text-slate-400 text-sm">{{ $manager->managedTeam?->name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $manager->isAdmin() ? 'bg-purple-500/20 text-purple-300' : 'bg-blue-500/20 text-blue-300' }}">
                                {{ ucfirst($manager->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $manager->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300' }}">
                                {{ $manager->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.managers.edit', $manager) }}" class="px-3 py-1 rounded text-sm font-bold bg-amber-600/20 text-amber-300 hover:bg-amber-600/30 transition inline-block">
                                Edit
                            </a>
                            @if (!$manager->isAdmin())
                                <form action="{{ route('admin.managers.destroy', $manager) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this manager?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 rounded text-sm font-bold bg-red-600/20 text-red-300 hover:bg-red-600/30 transition">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">No managers found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $managers->links() }}
    </div>
@endsection
