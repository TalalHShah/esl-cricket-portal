@extends('layouts.admin')

@section('title', 'Admin Control Panel')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white tracking-tight">Admin Control Panel</h1>
        <p class="mt-2 text-slate-400">Manage managers, teams, players, and league settings</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5 mb-8">
        <div class="cricket-card rounded-2xl p-6">
            <p class="text-xs font-bold uppercase text-emerald-400">Managers</p>
            <p class="text-3xl font-black text-white mt-2">{{ $stats['managers'] }}</p>
        </div>
        <div class="cricket-card rounded-2xl p-6">
            <p class="text-xs font-bold uppercase text-amber-400">Admins</p>
            <p class="text-3xl font-black text-white mt-2">{{ $stats['admins'] }}</p>
        </div>
        <div class="cricket-card rounded-2xl p-6">
            <p class="text-xs font-bold uppercase text-red-400">Teams</p>
            <p class="text-3xl font-black text-white mt-2">{{ $stats['teams'] }}</p>
        </div>
        <div class="cricket-card rounded-2xl p-6">
            <p class="text-xs font-bold uppercase text-blue-400">Players</p>
            <p class="text-3xl font-black text-white mt-2">{{ $stats['players'] }}</p>
        </div>
        <div class="cricket-card rounded-2xl p-6">
            <p class="text-xs font-bold uppercase text-cyan-400">Total Budget</p>
            <p class="text-lg font-black text-white mt-2"><x-money :amount="$stats['total_budget']" :size="16" /></p>
        </div>
    </div>

    {{-- Control Options --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Managers Management --}}
        <div class="cricket-card rounded-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-emerald-500/10 bg-gradient-to-r from-emerald-500/10">
                <h2 class="text-lg font-bold text-white">Managers</h2>
            </div>
            <div class="p-6 space-y-3">
                <a href="{{ route('admin.managers.index') }}" class="block w-full px-4 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-center transition">
                    View All Managers
                </a>
                <a href="{{ route('admin.managers.create') }}" class="block w-full px-4 py-3 rounded-lg bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 font-bold text-center transition border border-emerald-500/30">
                    + Add New Manager
                </a>
            </div>
        </div>

        {{-- Teams Management --}}
        <div class="cricket-card rounded-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-emerald-500/10 bg-gradient-to-r from-red-500/10">
                <h2 class="text-lg font-bold text-white">Teams</h2>
            </div>
            <div class="p-6 space-y-3">
                <a href="{{ route('admin.teams.index') }}" class="block w-full px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold text-center transition">
                    View All Teams
                </a>
                <a href="{{ route('admin.teams.create') }}" class="block w-full px-4 py-3 rounded-lg bg-red-600/20 hover:bg-red-600/30 text-red-300 font-bold text-center transition border border-red-500/30">
                    + Add New Team
                </a>
            </div>
        </div>

        {{-- Players Management --}}
        <div class="cricket-card rounded-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-emerald-500/10 bg-gradient-to-r from-amber-500/10">
                <h2 class="text-lg font-bold text-white">Players</h2>
            </div>
            <div class="p-6 space-y-3">
                <a href="{{ route('admin.players.index') }}" class="block w-full px-4 py-3 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-center transition">
                    View All Players
                </a>
                <a href="{{ route('admin.players.create') }}" class="block w-full px-4 py-3 rounded-lg bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 font-bold text-center transition border border-amber-500/30">
                    + Add New Player
                </a>
            </div>
        </div>

        {{-- Match Management --}}
        <div class="cricket-card rounded-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-emerald-500/10 bg-gradient-to-r from-cyan-500/10">
                <h2 class="text-lg font-bold text-white">Matches</h2>
            </div>
            <div class="p-6 space-y-3">
                <a href="{{ route('admin.matches.index') }}" class="block w-full px-4 py-3 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-center transition">
                    View All Matches
                </a>
                <a href="{{ route('admin.matches.create') }}" class="block w-full px-4 py-3 rounded-lg bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-300 font-bold text-center transition border border-cyan-500/30">
                    + Schedule Match
                </a>
            </div>
        </div>

        {{-- Auction Management --}}
        <div class="cricket-card rounded-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-emerald-500/10 bg-gradient-to-r from-orange-500/10">
                <h2 class="text-lg font-bold text-white">Auctions</h2>
            </div>
            <div class="p-6 space-y-3">
                <a href="{{ route('admin.auctions.index') }}" class="block w-full px-4 py-3 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-bold text-center transition">
                    Manage Auctions
                </a>
                <a href="{{ route('admin.auctions.create') }}" class="block w-full px-4 py-3 rounded-lg bg-orange-600/20 hover:bg-orange-600/30 text-orange-300 font-bold text-center transition border border-orange-500/30">
                    + Start New Auction
                </a>
            </div>
        </div>

        {{-- Settings Management --}}
        <div class="cricket-card rounded-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-emerald-500/10 bg-gradient-to-r from-blue-500/10">
                <h2 class="text-lg font-bold text-white">Settings</h2>
            </div>
            <div class="p-6 space-y-3">
                <a href="{{ route('admin.settings.index') }}" class="block w-full px-4 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-center transition">
                    League Settings
                </a>
                <p class="text-xs text-slate-400 text-center">Transfer windows, valuation rules, thresholds</p>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="mt-8 cricket-card rounded-2xl p-6">
        <h2 class="text-lg font-bold text-white mb-4">Quick Links</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold text-center transition">
                League Dashboard
            </a>
            <a href="{{ route('teams.index') }}" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold text-center transition">
                Public Teams
            </a>
            <a href="{{ route('players.index') }}" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold text-center transition">
                Public Players
            </a>
            <a href="{{ route('managers.index') }}" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold text-center transition">
                Public Managers
            </a>
        </div>
    </div>
@endsection
