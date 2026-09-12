<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;

class LiveStreamController extends Controller
{
    public function index(): View
    {
        $streamUrl = Setting::where('key', 'live_stream_url')->value('value');
        $youtubeUrl = Setting::where('key', 'youtube_channel_url')->value('value');

        return view('manager.livestream', compact('streamUrl', 'youtubeUrl'));
    }
}
