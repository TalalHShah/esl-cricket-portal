<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\Sortable;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $managers = User::where('role', 'manager')->get();
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
        ]);

        Team::create($validated);

        return redirect()->route('admin.teams.index')->with('status', "Team '{$validated['name']}' created.");
    }

    public function edit(Team $team)
    {
        $managers = User::where('role', 'manager')->get();
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
        ]);

        $team->update($validated);

        return redirect()->route('admin.teams.index')->with('status', "Team '{$team->name}' updated.");
    }

    public function destroy(Team $team)
    {
        $name = $team->name;
        $team->delete();
        return redirect()->route('admin.teams.index')->with('status', "Team '$name' deleted.");
    }
}
