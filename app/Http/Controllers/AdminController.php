<?php

namespace App\Http\Controllers;

use App\Models\AuctionDraft;
use App\Models\AuctionSession;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    /**
     * Display the admin control panel for CRUD operations.
     */
    public function panel(): View
    {
        $stats = [
            'managers' => User::where('role', 'manager')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'teams' => Team::count(),
            'players' => Player::count(),
            'total_budget' => Team::sum('budget'),
        ];

        $auctionStatus = \App\Models\Setting::auctionStatus();
        $transferWindowOpen = \App\Models\Setting::isTransferWindowOpen();

        $draft = AuctionDraft::latest()->first();
        $draftPoolCount = $draft ? AuctionSession::where('auction_draft_id', $draft->id)->where('status', 'scheduled')->count() : 0;
        $anyPlayersRemainForDraft = Player::query()->transferable()->notNominated()->whereNull('team_id')->where('is_active', true)->exists();

        $liveAuction = AuctionSession::whereIn('status', ['live', 'paused'])->with('player', 'highestBidder')->first();
        $scheduledAuctionCount = AuctionSession::where('status', 'scheduled')->count();

        $teams = Team::withCount('players')->orderBy('name')->get();

        return view('admin.panel', compact(
            'stats', 'auctionStatus', 'transferWindowOpen',
            'draft', 'draftPoolCount', 'anyPlayersRemainForDraft',
            'liveAuction', 'scheduledAuctionCount', 'teams'
        ));
    }
}
