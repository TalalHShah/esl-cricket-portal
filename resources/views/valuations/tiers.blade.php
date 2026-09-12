@extends('layouts.app')

@section('title', 'Players by Tier')

@section('content')
    @include('partials.page-header', [
        'title' => 'Players by Tier',
        'eyebrow' => 'Market',
        'subtitle' => 'Performance categories and average valuations',
    ])

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($tiers as $tier)
            <div class="card-section p-6">
                <p class="eyebrow gold mb-4">{{ $tier->tier }}</p>
                <div class="space-y-3">
                    <div class="flex justify-between items-baseline">
                        <span class="text-sm" style="color: var(--paper-faint);">Count</span>
                        <span class="text-lg font-semibold" style="color: var(--paper);">{{ $tier->count }}</span>
                    </div>
                    <div class="flex justify-between items-baseline">
                        <span class="text-sm" style="color: var(--paper-faint);">Avg Value</span>
                        <span class="text-lg font-semibold" style="color: var(--gold);">{{ number_format($tier->avg_value, 0) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                @include('partials.empty-state', ['message' => 'No tier data available.'])
            </div>
        @endforelse
    </div>

    <div class="mt-10">
        <a href="{{ route('valuations.league') }}" class="btn-ghost px-6 py-3">&larr; Back to League View</a>
    </div>
@endsection
