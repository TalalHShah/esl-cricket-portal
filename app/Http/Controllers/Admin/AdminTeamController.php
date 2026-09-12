<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\Sortable;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AdminTeamController extends Controller
{
    use Sortable;

    public function index(Request $request)
    {
        $query = Team::with('manager');

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

        $team->delete();
        return redirect()->route('admin.teams.index')->with('status', "Team '$name' deleted.");
    }
}
