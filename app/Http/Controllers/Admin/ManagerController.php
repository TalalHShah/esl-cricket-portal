<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\Sortable;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ManagerController extends Controller
{
    use Sortable;

    public function index(Request $request)
    {
        $query = User::where('role', 'manager')->orWhere('role', 'admin');

        $sort = $this->applySort($query, $request, [
            'name_asc' => fn ($q) => $q->orderBy('name'),
            'name_desc' => fn ($q) => $q->orderByDesc('name'),
            'email_asc' => fn ($q) => $q->orderBy('email'),
            'role' => fn ($q) => $q->orderBy('role'),
            'status' => fn ($q) => $q->orderByDesc('is_active'),
        ], 'name_asc');

        $managers = $query->paginate(10)->withQueryString();

        return view('admin.managers.index', compact('managers', 'sort'));
    }

    public function create()
    {
        $teams = Team::all();
        return view('admin.managers.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'team_id' => 'required|exists:teams,id',
            'is_active' => 'boolean',
        ]);

        $manager = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'manager',
            'is_active' => $request->boolean('is_active', true),
        ]);

        Team::find($validated['team_id'])->update(['manager_id' => $manager->id]);

        return redirect()->route('admin.managers.index')->with('status', "Manager '{$manager->name}' created successfully.");
    }

    public function edit(User $manager)
    {
        if (!in_array($manager->role, ['manager', 'admin'])) {
            abort(404);
        }
        $teams = Team::all();
        return view('admin.managers.edit', compact('manager', 'teams'));
    }

    public function update(Request $request, User $manager)
    {
        if (!in_array($manager->role, ['manager', 'admin'])) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $manager->id,
            'team_id' => 'required|exists:teams,id',
            'is_active' => 'boolean',
        ]);

        $manager->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active'),
        ]);

        Team::where('manager_id', $manager->id)->update(['manager_id' => null]);
        Team::find($validated['team_id'])->update(['manager_id' => $manager->id]);

        return redirect()->route('admin.managers.index')->with('status', "Manager '{$manager->name}' updated.");
    }

    public function destroy(User $manager)
    {
        if ($manager->isAdmin()) {
            return redirect()->back()->with('error', 'Cannot delete admin accounts.');
        }

        Team::where('manager_id', $manager->id)->update(['manager_id' => null]);
        $name = $manager->name;
        $manager->delete();

        return redirect()->route('admin.managers.index')->with('status', "Manager '$name' deleted.");
    }
}
