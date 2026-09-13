<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\AuctionSession;
use App\Models\Player;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PlayerController extends Controller
{
    public function show(Player $player): View
    {
        $player->load('team');

        $ownTeam = Auth::user()->managedTeam;
        $windowOpen = Setting::isTransferWindowOpen();
        $isShortlisted = $ownTeam ? $ownTeam->shortlistedPlayers()->where('players.id', $player->id)->exists() : false;
        $activeAuctionSession = AuctionSession::where('player_id', $player->id)
            ->whereIn('status', ['scheduled', 'live', 'paused'])
            ->first();

        $stats = $player->matchStats()
            ->with(['match.homeTeam', 'match.awayTeam'])
            ->orderByDesc('created_at')
            ->get();

        $totals = [
            'matches' => $stats->count(),
            'runs' => $stats->sum('runs_scored'),
            'balls' => $stats->sum('balls_faced'),
            'fours' => $stats->sum('fours'),
            'sixes' => $stats->sum('sixes'),
            'wickets' => $stats->sum('wickets_taken'),
            'overs' => $stats->sum('overs_bowled'),
            'catches' => $stats->sum('catches'),
            'stumpings' => $stats->sum('stumpings'),
        ];

        $transfers = $player->transfers()
            ->with(['fromTeam', 'toTeam'])
            ->orderByDesc('created_at')
            ->get();

        return view('manager.player-show', compact(
            'player', 'stats', 'totals', 'transfers', 'ownTeam', 'windowOpen', 'isShortlisted', 'activeAuctionSession'
        ));
    }
}
