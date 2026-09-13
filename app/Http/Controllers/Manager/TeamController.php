<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Transfer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function show(Request $request): View
    {
        $team = Auth::user()->managedTeam;
        $sort = $request->input('sort', 'value_desc');
        $view = in_array($request->input('view'), ['table', 'compact'], true) ? $request->input('view') : 'grid';

        $players = collect();

        if ($team) {
            $query = $team->players();

            match ($sort) {
                'name_asc' => $query->orderBy('name'),
                'name_desc' => $query->orderByDesc('name'),
                'value_asc' => $query->orderBy('current_value'),
                'value_desc' => $query->orderByDesc('current_value'),
                'role' => $query->orderBy('role')->orderByDesc('current_value'),
                'tier' => $query->orderByRaw("FIELD(tier, 'Platinum', 'Diamond', 'Gold', 'Silver')"),
                'age_asc' => $query->orderBy('age'),
                'age_desc' => $query->orderByDesc('age'),
                default => $query->orderByDesc('current_value'),
            };

            $players = $query->get();
        }

        return view('manager.team', compact('team', 'players', 'sort', 'view'));
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team) {
            return back()->withErrors(['logo' => 'You are not assigned to manage a team.']);
        }

        $validated = $request->validate([
            'logo' => 'required|image|max:2048',
        ]);

        if ($team->logo) {
            Storage::disk('public')->delete($team->logo);
        }

        $team->update(['logo' => $request->file('logo')->store('team-logos', 'public')]);

        return back()->with('status', 'Team logo updated.');
    }

    public function updateHomeGround(Request $request): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team) {
            return back()->withErrors(['home_ground' => 'You are not assigned to manage a team.']);
        }

        $validated = $request->validate([
            'home_ground_name' => 'nullable|string|max:255',
            'home_ground_location' => 'nullable|string|max:255',
            'home_ground_image' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('home_ground_image')) {
            if ($team->home_ground_image) {
                Storage::disk('public')->delete($team->home_ground_image);
            }
            $validated['home_ground_image'] = $request->file('home_ground_image')->store('home-grounds', 'public');
        } else {
            unset($validated['home_ground_image']);
        }

        $team->update($validated);

        return back()->with('status', 'Home ground updated.');
    }

    /**
     * Drop a player from the squad, freeing up a roster slot so the
     * manager can sign or negotiate for someone else. Money already
     * spent isn't refunded — only the squad slot is freed — the same
     * as releasing a player in most fantasy-auction leagues.
     */
    public function releasePlayer(Player $player): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team) {
            return back()->withErrors(['release' => 'You are not assigned to manage a team.']);
        }

        if ($player->team_id !== $team->id) {
            return back()->withErrors(['release' => 'That player is not on your squad.']);
        }

        if ($player->is_manager_player) {
            return back()->withErrors(['release' => 'You cannot release yourself.']);
        }

        if (! Setting::isTransferWindowOpen()) {
            $message = Setting::auctionStatus() !== 'closed'
                ? 'The auction is ' . Setting::auctionStatus() . ' — squad changes are paused until it closes.'
                : 'The transfer window is currently closed.';

            return back()->withErrors(['release' => $message]);
        }

        DB::transaction(function () use ($player, $team) {
            $locked = Player::whereKey($player->id)->lockForUpdate()->first();

            if ($locked->team_id !== $team->id) {
                return;
            }

            $locked->update(['team_id' => null, 'sold_price' => null]);

            Transfer::create([
                'player_id' => $locked->id,
                'from_team_id' => $team->id,
                'to_team_id' => null,
                'fee' => 0,
                'type' => 'release',
                'status' => 'approved',
                'requested_by_user_id' => Auth::id(),
                'approved_by_user_id' => Auth::id(),
                'effective_at' => now(),
                'notes' => "Released {$locked->name} from {$team->name}",
            ]);
        });

        return back()->with('status', "{$player->name} released — they're a free agent again. Your squad slot is open, but the budget you spent isn't refunded.");
    }
}
