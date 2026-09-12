<?php

namespace App\Http\Controllers\Admin;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminPlayerController extends Controller
{
    public function index()
    {
        $players = Player::with('team')->paginate(15);
        return view('admin.players.index', compact('players'));
    }

    public function create()
    {
        $teams = Team::all();
        $tiers = ['Superstar', 'Star', 'Normal', 'Low-value'];
        $roles = ['Batsman', 'Bowler', 'All-rounder', 'Wicket-keeper'];
        return view('admin.players.create', compact('teams', 'tiers', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => 'nullable|exists:teams,id',
            'role' => 'required|in:Batsman,Bowler,All-rounder,Wicket-keeper',
            'tier' => 'required|in:Superstar,Star,Normal,Low-value',
            'base_value' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        Player::create($validated);

        return redirect()->route('admin.players.index')->with('status', "Player '{$validated['name']}' created.");
    }

    public function edit(Player $player)
    {
        $teams = Team::all();
        $tiers = ['Superstar', 'Star', 'Normal', 'Low-value'];
        $roles = ['Batsman', 'Bowler', 'All-rounder', 'Wicket-keeper'];
        return view('admin.players.edit', compact('player', 'teams', 'tiers', 'roles'));
    }

    public function update(Request $request, Player $player)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => 'nullable|exists:teams,id',
            'role' => 'required|in:Batsman,Bowler,All-rounder,Wicket-keeper',
            'tier' => 'required|in:Superstar,Star,Normal,Low-value',
            'base_value' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

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
