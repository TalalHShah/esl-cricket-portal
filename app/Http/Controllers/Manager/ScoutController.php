<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ScoutController extends Controller
{
    public function index(Request $request): View
    {
        $query = Player::query()->whereNull('team_id')->where('is_active', true);

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('tier')) {
            $query->where('tier', $request->input('tier'));
        }

        if ($request->filled('country')) {
            $query->where('country', 'like', '%' . $request->input('country') . '%');
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $players = $query->orderByDesc('current_value')->paginate(12)->withQueryString();

        $roles = ['Batsman', 'Wicketkeeper', 'All-rounder', 'Fast Bowler', 'Spinner'];
        $tiers = ['Superstar', 'Star', 'Normal', 'Low-value'];

        return view('manager.scouts', compact('players', 'roles', 'tiers'));
    }
}
