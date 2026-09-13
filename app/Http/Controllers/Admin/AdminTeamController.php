<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\Sortable;
use App\Models\CricketMatch;
use App\Models\Player;
use App\Models\Team;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminTeamController extends Controller
{
    use Sortable;

    public function index(Request $request)
    {
        $query = Team::with('manager')->withCount('players');

        $sort = $this->applySort($query, $request, [
            'name_asc' => fn ($q) => $q->orderBy('name'),
            'name_desc' => fn ($q) => $q->orderByDesc('name'),
            'budget_desc' => fn ($q) => $q->orderByDesc('budget'),
            'budget_asc' => fn ($q) => $q->orderBy('budget'),
            'spent_desc' => fn ($q) => $q->orderByDesc('spent'),
            'remaining_desc' => fn ($q) => $q->orderByRaw('(budget - spent) desc'),
        ], 'name_asc');

        $teams = $query->paginate(10)->withQueryString();

        return view('admin.teams.index', compact('teams', 'sort'));
    }

    public function create()
    {
        $managers = User::whereIn('role', ['manager', 'admin'])->orderBy('name')->get();
        return view('admin.teams.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams',
            'manager_id' => 'nullable|exists:users,id',
            'budget' => 'required|numeric|min:0',
            'primary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'secondary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('team-logos', 'public');
        }

        Team::create($validated);

        return redirect()->route('admin.teams.index')->with('status', "Team '{$validated['name']}' created.");
    }

    public function edit(Team $team)
    {
        $managers = User::whereIn('role', ['manager', 'admin'])->orderBy('name')->get();
        return view('admin.teams.edit', compact('team', 'managers'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $team->id,
            'manager_id' => 'nullable|exists:users,id',
            'budget' => 'required|numeric|min:0',
            'primary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'secondary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'logo' => 'nullable|image|max:2048',
            'remove_logo' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_logo') && $team->logo) {
            Storage::disk('public')->delete($team->logo);
            $validated['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($team->logo) {
                Storage::disk('public')->delete($team->logo);
            }
            $validated['logo'] = $request->file('logo')->store('team-logos', 'public');
        }

        unset($validated['remove_logo']);

        $team->update($validated);

        return redirect()->route('admin.teams.index')->with('status', "Team '{$team->name}' updated.");
    }

    public function destroy(Team $team)
    {
        $name = $team->name;

        $matchCount = CricketMatch::where('home_team_id', $team->id)
            ->orWhere('away_team_id', $team->id)
            ->count();

        if ($matchCount > 0) {
            return back()->withErrors(['team' => "Cannot delete '{$name}' — it has {$matchCount} match record(s). Deleting it would cascade-delete match and stat history for the opposing team(s) as well. Remove or reassign those matches first."]);
        }

        if ($team->players()->exists()) {
            return back()->withErrors(['team' => "Cannot delete '{$name}' — it still has signed players. Release them to free agency first."]);
        }

        if ($team->logo) {
            Storage::disk('public')->delete($team->logo);
        }

        $team->delete();
        return redirect()->route('admin.teams.index')->with('status', "Team '$name' deleted.");
    }

    /**
     * Admin roster control for one team — release any signed player
     * (or the whole roster at once) straight back to free agency, with
     * their spent budget refunded. Unlike a manager's own Release
     * button, this isn't gated by the transfer window: the admin is
     * doing this deliberately, not as a live game action.
     */
    public function roster(Team $team)
    {
        $players = $team->players()->orderByDesc('current_value')->get();

        return view('admin.teams.roster', compact('team', 'players'));
    }

    public function removePlayer(Team $team, Player $player)
    {
        if ($player->team_id !== $team->id) {
            return back()->withErrors(['roster' => 'That player is not on this team.']);
        }

        if ($player->is_manager_player) {
            return back()->withErrors(['roster' => 'A manager\'s own player cannot be removed from their team.']);
        }

        DB::transaction(function () use ($team, $player) {
            $locked = Player::whereKey($player->id)->lockForUpdate()->first();
            $lockedTeam = Team::whereKey($team->id)->lockForUpdate()->first();

            if ($locked->team_id !== $lockedTeam->id) {
                return;
            }

            $refund = min((float) ($locked->sold_price ?? 0), (float) $lockedTeam->spent);

            $lockedTeam->decrement('spent', $refund);

            $locked->update(['team_id' => null, 'sold_price' => null]);

            Transfer::create([
                'player_id' => $locked->id,
                'from_team_id' => $lockedTeam->id,
                'to_team_id' => null,
                'fee' => 0,
                'type' => 'release',
                'status' => 'approved',
                'requested_by_user_id' => Auth::id(),
                'approved_by_user_id' => Auth::id(),
                'effective_at' => now(),
                'notes' => "Admin removed {$locked->name} from {$lockedTeam->name} — PKR " . number_format($refund, 0) . ' refunded to budget.',
            ]);
        });

        return back()->with('status', "{$player->name} removed from {$team->name} and their budget refunded.");
    }

    public function removeAllPlayers(Team $team)
    {
        DB::transaction(function () use ($team) {
            $lockedTeam = Team::whereKey($team->id)->lockForUpdate()->first();

            $players = Player::where('team_id', $lockedTeam->id)
                ->where('is_manager_player', false)
                ->lockForUpdate()
                ->get();

            foreach ($players as $player) {
                $refund = min((float) ($player->sold_price ?? 0), (float) $lockedTeam->spent);

                $lockedTeam->decrement('spent', $refund);

                $player->update(['team_id' => null, 'sold_price' => null]);

                Transfer::create([
                    'player_id' => $player->id,
                    'from_team_id' => $lockedTeam->id,
                    'to_team_id' => null,
                    'fee' => 0,
                    'type' => 'release',
                    'status' => 'approved',
                    'requested_by_user_id' => Auth::id(),
                    'approved_by_user_id' => Auth::id(),
                    'effective_at' => now(),
                    'notes' => "Admin cleared {$player->name} from {$lockedTeam->name}'s roster — PKR " . number_format($refund, 0) . ' refunded to budget.',
                ]);
            }
        });

        return redirect()->route('admin.teams.roster', $team)->with('status', "{$team->name}'s roster has been cleared and their budget refunded.");
    }
}
