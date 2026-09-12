@extends('layouts.manager')

@section('title', 'Live Stream')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">Broadcast</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Live Stream</h1>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="card-section">
                <div class="card-header"><h2>Live Now</h2></div>
                <div class="p-6">
                    @if($streamUrl)
                        <div class="aspect-video" style="border: 1px solid var(--line);">
                            <iframe src="{{ $streamUrl }}" class="w-full h-full" allowfullscreen></iframe>
                        </div>
                    @else
                        <div class="aspect-video flex items-center justify-center text-center" style="background-color: var(--surface-raised); border: 1px solid var(--line);">
                            <div>
                                <p class="eyebrow gold mb-2">No Broadcast Active</p>
                                <p class="text-sm" style="color: var(--paper-faint);">Check back during match time</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="card-section">
                <div class="card-header"><h2>Channel</h2></div>
                <div class="p-6">
                    @if($youtubeUrl)
                        <a href="{{ $youtubeUrl }}" target="_blank" class="btn-accent w-full py-3 block text-center">Visit Channel</a>
                    @else
                        <div class="aspect-video flex items-center justify-center text-center" style="background-color: var(--surface-raised); border: 1px solid var(--line);">
                            <p class="text-sm" style="color: var(--paper-faint);">Channel link not set</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
