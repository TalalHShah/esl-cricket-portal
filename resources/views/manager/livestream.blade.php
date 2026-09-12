@extends('layouts.manager')

@section('title', 'Live Stream')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white mb-2 flex items-center gap-2"><span>🔴</span> Live Stream</h1>
        <p class="text-lg text-slate-400">Watch matches and league broadcasts live</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="card-section rounded overflow-hidden">
                <div class="card-header flex items-center gap-2">
                    <span>📺</span>
                    <h2>Live Now</h2>
                </div>
                <div class="p-6">
                    @if($streamUrl)
                        <div class="aspect-video rounded overflow-hidden">
                            <iframe src="{{ $streamUrl }}" class="w-full h-full" allowfullscreen></iframe>
                        </div>
                    @else
                        <div class="aspect-video rounded flex items-center justify-center text-slate-500 text-center"
                             style="background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-dark) 100%);">
                            <div>
                                <p class="text-lg font-semibold mb-2">📺 No Live Stream Active</p>
                                <p class="text-sm">Check back during match time</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="card-section rounded overflow-hidden">
                <div class="card-header flex items-center gap-2">
                    <span>▶️</span>
                    <h2>YouTube Channel</h2>
                </div>
                <div class="p-6">
                    @if($youtubeUrl)
                        <a href="{{ $youtubeUrl }}" target="_blank" class="block w-full text-center btn-accent py-3 text-sm font-bold rounded">
                            Visit Channel
                        </a>
                    @else
                        <div class="aspect-video rounded flex items-center justify-center text-slate-500 text-center mb-4"
                             style="background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-dark) 100%);">
                            <p class="text-sm">▶️ Channel link not set</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
