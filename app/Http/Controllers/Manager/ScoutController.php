<?php

namespace App\Http\Controllers\Manager;

use App\Concerns\Sortable;
use App\Http\Controllers\Controller;
use App\Models\AuctionSession;
use App\Models\Player;
use App\Models\PlayerShortlist;
use App\Models\Setting;
use App\Services\MarketAuctionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScoutController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $ownTeam = Auth::user()->managedTeam;
        $windowOpen = Setting::isTransferWindowOpen();

        $query = Player::query()->transferable()->with('team')->where('is_active', true);

        if ($ownTeam) {
            $query->where(function ($q) use ($ownTeam) {
                $q->whereNull('team_id')->orWhere('team_id', '!=', $ownTeam->id);
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('tier')) {
            $query->where('tier', $request->input('tier'));
        }

        if ($request->filled('country')) {
            $query->where('country', 'like', '%' . $request->input('country') . '%');
        }

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->input('team_id'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->input('availability') === 'free') {
            $query->whereNull('team_id');
        } elseif ($request->input('availability') === 'signed') {
            $query->whereNotNull('team_id');
        }

        $shortlistedIds = $ownTeam ? $ownTeam->shortlistedPlayers()->pluck('players.id')->all() : [];

        if ($request->boolean('shortlisted')) {
            $query->whereIn('players.id', $shortlistedIds ?: [0]);
        }

        $sort = $this->applySort($query, $request, [
            'default' => fn ($q) => $q->orderByRaw('team_id IS NULL DESC')->orderByDesc('current_value'),
            'value_desc' => fn ($q) => $q->orderByDesc('current_value'),
            'value_asc' => fn ($q) => $q->orderBy('current_value'),
            'name_asc' => fn ($q) => $q->orderBy('name'),
            'name_desc' => fn ($q) => $q->orderByDesc('name'),
            'role' => fn ($q) => $q->orderBy('role')->orderByDesc('current_value'),
            'tier' => fn ($q) => $q->orderByRaw("FIELD(tier, 'Platinum', 'Diamond', 'Gold', 'Silver')"),
            'age_asc' => fn ($q) => $q->orderBy('age'),
            'age_desc' => fn ($q) => $q->orderByDesc('age'),
            'country_asc' => fn ($q) => $q->orderBy('country'),
        ], 'default');

        $players = $query->paginate(12)->withQueryString();

        $activeAuctions = AuctionSession::whereIn('player_id', $players->pluck('id'))
            ->whereIn('status', ['scheduled', 'live', 'paused'])
            ->pluck('id', 'player_id');

        $roles = ['Batsman', 'All-rounder', 'Bowler', 'Wicketkeeper'];
        $tiers = ['Platinum', 'Diamond', 'Gold', 'Silver'];
        $teams = \App\Models\Team::orderBy('name')->get(['id', 'name']);

        return view('manager.scouts', compact('players', 'roles', 'tiers', 'teams', 'windowOpen', 'sort', 'shortlistedIds', 'activeAuctions'));
    }

    public function toggleShortlist(Player $player): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team) {
            return back()->withErrors(['shortlist' => 'You are not assigned to manage a team.']);
        }

        $existing = PlayerShortlist::where('team_id', $team->id)->where('player_id', $player->id)->first();

        if ($existing) {
            $existing->delete();

            return back()->with('status', "{$player->name} removed from your shortlist.");
        }

        PlayerShortlist::create(['team_id' => $team->id, 'player_id' => $player->id]);

        return back()->with('status', "{$player->name} added to your shortlist.");
    }

    /**
     * "Sign Player" no longer transfers a free agent instantly — it
     * opens (or joins, if one's already running) a one-hour auction so
     * the rest of the league gets a real chance to outbid, instead of
     * whoever clicks first winning outright with no recourse for anyone
     * else.
     */
    public function openAuction(Player $player, MarketAuctionService $service): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team) {
            return back()->withErrors(['sign' => 'You are not assigned to manage a team.']);
        }

        if (! Setting::isTransferWindowOpen()) {
            $message = Setting::auctionStatus() !== 'closed'
                ? 'The auction is ' . Setting::auctionStatus() . " — you can't purchase players right now, only shortlist them for later."
                : 'The transfer window is currently closed.';

            return back()->withErrors(['sign' => $message]);
        }

        if ($player->is_manager_player) {
            return back()->withErrors(['sign' => 'This player cannot be signed.']);
        }

        $result = $service->open($player, $team->id);

        if (isset($result['error'])) {
            return back()->withErrors(['sign' => $result['error']]);
        }

        if ($result['already_open']) {
            return redirect()->route('manager.auction.room', $result['session'])
                ->with('status', "{$player->name} already has a live auction — you're in the room to bid.");
        }

        return redirect()->route('manager.auction.room', $result['session'])
            ->with('status', "Auction opened for {$player->name} at their current value — the whole league has one hour to outbid you.");
    }
}
