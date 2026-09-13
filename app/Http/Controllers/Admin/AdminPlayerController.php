<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\Sortable;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AdminPlayerController extends Controller
{
    use Sortable;

    private const ROLES = ['Batsman', 'All-rounder', 'Bowler', 'Wicketkeeper'];
    private const TIERS = ['Platinum', 'Diamond', 'Gold', 'Silver'];

    private const BATTING_STYLES = ['Right-Hand Bat', 'Left-Hand Bat'];

    private const BOWLING_STYLES = [
        'Right-arm Fast',
        'Right-arm Fast-Medium',
        'Right-arm Medium',
        'Right-arm Off Spin',
        'Right-arm Leg Spin',
        'Left-arm Fast',
        'Left-arm Fast-Medium',
        'Left-arm Medium',
        'Left-arm Orthodox',
        'Left-arm Chinaman',
    ];

    public function index(Request $request)
    {
        $query = Player::with('team');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('team_id')) {
            if ($request->input('team_id') === 'free_agent') {
                $query->whereNull('team_id');
            } else {
                $query->where('team_id', $request->input('team_id'));
            }
        }

        if ($request->filled('country')) {
            $query->where('country', 'like', '%' . $request->input('country') . '%');
        }

        if ($request->filled('tier')) {
            $query->where('tier', $request->input('tier'));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $sort = $this->applySort($query, $request, [
            'name_asc' => fn ($q) => $q->orderBy('name'),
            'name_desc' => fn ($q) => $q->orderByDesc('name'),
            'value_desc' => fn ($q) => $q->orderByDesc('current_value'),
            'value_asc' => fn ($q) => $q->orderBy('current_value'),
            'tier' => fn ($q) => $q->orderByRaw("FIELD(tier, 'Platinum', 'Diamond', 'Gold', 'Silver')"),
            'role' => fn ($q) => $q->orderBy('role'),
            'status' => fn ($q) => $q->orderByDesc('is_active'),
        ], 'name_asc');

        $players = $query->paginate(15)->withQueryString();

        $teams = Team::orderBy('name')->get(['id', 'name']);

        return view('admin.players.index', compact('players', 'sort', 'teams'));
    }

    public function create()
    {
        $teams = Team::all();
        $tiers = self::TIERS;
        $roles = self::ROLES;
        $battingStyles = self::BATTING_STYLES;
        $bowlingStyles = self::BOWLING_STYLES;
        return view('admin.players.create', compact('teams', 'tiers', 'roles', 'battingStyles', 'bowlingStyles'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlayer($request);

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
        $battingStyles = self::BATTING_STYLES;
        $bowlingStyles = self::BOWLING_STYLES;
        return view('admin.players.edit', compact('player', 'teams', 'tiers', 'roles', 'battingStyles', 'bowlingStyles'));
    }

    public function update(Request $request, Player $player)
    {
        $validated = $this->validatePlayer($request);

        if ($request->boolean('remove_photo') && $player->image) {
            Storage::disk('public')->delete($player->image);
            $validated['image'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($player->image) {
                Storage::disk('public')->delete($player->image);
            }
            $validated['image'] = $request->file('photo')->store('player-photos', 'public');
        }

        unset($validated['photo'], $validated['remove_photo']);

        $player->update($validated);

        return redirect()->route('admin.players.index')->with('status', "Player '{$player->name}' updated.");
    }

    public function destroy(Player $player)
    {
        $name = $player->name;
        if ($player->image) {
            Storage::disk('public')->delete($player->image);
        }
        $player->delete();
        return redirect()->route('admin.players.index')->with('status', "Player '$name' deleted.");
    }

    /**
     * Validate a player submission, requiring the sub-type field(s) that
     * apply to the chosen role: batting hand for Batsman/Wicketkeeper,
     * bowling arm+type for Bowler, both for All-rounder.
     */
    private function validatePlayer(Request $request): array
    {
        $role = $request->input('role');

        $battingRequired = in_array($role, ['Batsman', 'Wicketkeeper', 'All-rounder'], true);
        $bowlingRequired = in_array($role, ['Bowler', 'All-rounder'], true);

        return $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:64',
            'team_id' => 'nullable|exists:teams,id',
            'role' => 'required|in:' . implode(',', self::ROLES),
            'batting_style' => ($battingRequired ? 'required' : 'nullable') . '|in:' . implode(',', self::BATTING_STYLES),
            'bowling_style' => ($bowlingRequired ? 'required' : 'nullable') . '|in:' . implode(',', self::BOWLING_STYLES),
            'tier' => 'required|in:' . implode(',', self::TIERS),
            'base_value' => 'required|numeric|min:0',
            'age' => 'nullable|integer|min:14|max:50',
            'photo' => 'nullable|image|max:4096',
            'remove_photo' => 'nullable|boolean',
            'is_active' => 'boolean',
        ]);
    }
}
