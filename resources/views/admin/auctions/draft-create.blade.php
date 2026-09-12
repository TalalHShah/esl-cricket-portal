@extends('layouts.admin')

@section('title', 'Start Country Draft')

@section('content')
    @include('partials.page-header', ['title' => 'Start Country Draft', 'eyebrow' => 'Auction'])

    <div class="card-section p-8 max-w-2xl">
        <p class="text-sm mb-6" style="color: var(--paper-faint);">
            Set the pick order for the draft. On each turn, a random country is spun for the team whose turn it is,
            then every manager takes turns nominating a free agent from that country (opening the bid at their base
            value) or passing, until the country is exhausted or everyone passes in a row.
        </p>

        <form action="{{ route('admin.draft.store') }}" method="POST" class="space-y-4">
            @csrf

            @foreach ($teams as $i => $team)
                <div class="flex items-center gap-4">
                    <span class="eyebrow gold" style="width: 2rem;">#{{ $i + 1 }}</span>
                    <select name="team_order[]" class="field flex-1 px-4 py-3" required>
                        <option value="">Select a team...</option>
                        @foreach ($teams as $option)
                            <option value="{{ $option->id }}" @selected(old('team_order.' . $i) == $option->id)>
                                {{ $option->name }} — {{ $option->manager?->name ?? 'No manager' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            @error('team_order') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-accent flex-1 py-3">Start Draft</button>
                <a href="{{ route('admin.auctions.index') }}" class="btn-ghost flex-1 py-3 text-center">Cancel</a>
            </div>
        </form>
    </div>
@endsection
