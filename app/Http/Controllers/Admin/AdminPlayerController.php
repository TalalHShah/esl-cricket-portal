<?php

namespace App\Http\Controllers\Admin;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminPlayerController extends Controller
{
    private const ROLES = ['Batsman', 'Wicketkeeper', 'All-rounder', 'Fast Bowler', 'Spinner'];
    private const TIERS = ['Superstar', 'Star', 'Normal', 'Low-value'];

    public function index()
    {
        $players = Player::with('team')->paginate(15);
        return view('admin.players.index', compact('players'));
    }

    public function create()
    {
        $teams = Team::all();
        $tiers = self::TIERS;
        $roles = self::ROLES;
        return view('admin.players.create', compact('teams', 'tiers', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:64',
            'team_id' => 'nullable|exists:teams,id',
            'role' => 'required|in:' . implode(',', self::ROLES),
            'tier' => 'required|in:' . implode(',', self::TIERS),
            'base_value' => 'required|numeric|min:0',
            'age' => 'nullable|integer|min:14|max:50',
            'photo' => 'nullable|image|max:4096',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $validated['image'] = $request->file('photo')->store('player-photos', 'public');
        }
        unset($validated['photo']);

        $player = Player::create($validated);

        return redirect()->route('admin.players.index')->with('status', "Player '{$player->name}' created.");
    }

    public function edit(Player $player)
    {
        $teams = Team::all();
        $tiers = self::TIERS;
        $roles = self::ROLES;
        return view('admin.players.edit', compact('player', 'teams', 'tiers', 'roles'));
    }

    public function update(Request $request, Player $player)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:64',
            'team_id' => 'nullable|exists:teams,id',
            'role' => 'required|in:' . implode(',', self::ROLES),
            'tier' => 'required|in:' . implode(',', self::TIERS),
            'base_value' => 'required|numeric|min:0',
            'age' => 'nullable|integer|min:14|max:50',
            'photo' => 'nullable|image|max:4096',
            'remove_photo' => 'nullable|boolean',
            'is_active' => 'boolean',
        ]);

        if ($request->boolean('remove_photo')) {
            $validated['image'] = null;
        }

        if ($request->hasFile('photo')) {
            $validated['image'] = $request->file('photo')->store('player-photos', 'public');
        }

        unset($validated['photo'], $validated['remove_photo']);

        $player->update($validated);

        return redirect()->route('admin.players.index')->with('status', "Player '{$player->name}' updated.");
    }

    public function destroy(Player $player)
    {
        $name = $player->name;
        $player->delete();
        return redirect()->route('admin.players.index')->with('status', "Player '$name' deleted.");
    }
}
