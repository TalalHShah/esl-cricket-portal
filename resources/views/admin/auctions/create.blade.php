@extends('layouts.admin')

@section('title', 'New Auction')

@section('content')
    @include('partials.page-header', ['title' => 'New Auction Session', 'eyebrow' => 'Auction'])

    <div class="card-section p-8 max-w-2xl">
        <form action="{{ route('admin.auctions.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="eyebrow block mb-2">Player</label>
                <select name="player_id" class="field w-full px-4 py-3" required>
                    <option value="">Select a free agent...</option>
                    @foreach ($players as $player)
                        <option value="{{ $player->id }}" @selected(old('player_id') == $player->id)>
                            {{ $player->name }} — {{ $player->typeLabel() }} — {{ $player->country }} ({{ $player->tier }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs mt-2" style="color: var(--paper-faint);">Only free agents can be put up for auction. {{ $players->count() }} available.</p>
                @error('player_id') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="eyebrow block mb-2">Starting Bid</label>
                    <input type="number" name="starting_bid" class="field w-full px-4 py-3" value="{{ old('starting_bid', 500000) }}" min="1" required>
                    @error('starting_bid') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="eyebrow block mb-2">Bid Increment</label>
                    <input type="number" name="bid_increment" class="field w-full px-4 py-3" value="{{ old('bid_increment', 100000) }}" min="1" required>
                    @error('bid_increment') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-accent flex-1 py-3">Create Auction</button>
                <a href="{{ route('admin.auctions.index') }}" class="btn-ghost flex-1 py-3 text-center">Cancel</a>
            </div>
        </form>
    </div>
@endsection
