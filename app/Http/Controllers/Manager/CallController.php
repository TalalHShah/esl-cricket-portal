<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    /**
     * A minimal full-page video/voice call, meant to be opened as its
     * own popup window so it survives navigating around the rest of
     * the site in the main tab (a full page reload elsewhere can't
     * kill a call running in a separate window).
     */
    public function show(string $roomKey): View
    {
        abort_unless(preg_match('/^[A-Za-z0-9\-]{1,64}$/', $roomKey), 404);

        return view('manager.call', [
            'roomName' => 'ESLCricket-' . $roomKey,
            'title' => 'League Call',
            'displayName' => Auth::user()->name ?? 'Manager',
        ]);
    }
}
