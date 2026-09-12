@extends('layouts.app')

@section('title', 'Players by Tier')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white">⭐ Players by Tier</h1>
        <p class="mt-2 text-slate-400">Performance categories and average valuations</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        @forelse ($tiers as $tier)
            <div class="cricket-card rounded-2xl p-6">
                <h3 class="font-bold text-lg mb-3 {{ match($tier->tier) {
                    'Superstar' => 'text-purple-400',
                    'Star' => 'text-amber-400',
                    'Normal' => 'text-blue-400',
                    'Low-value' => 'text-slate-400',
                } }}">
                    {{ $tier->tier }}
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-400">Count:</span>
                        <span class="text-lg font-bold text-white">{{ $tier->count }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-400">Avg Value:</span>
                        <span class="text-lg font-bold text-emerald-400">PKR {{ number_format($tier->avg_value, 0) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-slate-500">No tier data</div>
        @endforelse
    </div>

    <div class="mt-8">
        <a href="{{ route('valuations.league') }}" class="px-6 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition">
            ← Back to League View
        </a>
    </div>
@endsection
