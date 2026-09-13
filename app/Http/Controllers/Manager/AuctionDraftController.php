<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\AuctionDraft;
use App\Models\AuctionSession;
use App\Models\Player;
use App\Models\Team;
use App\Services\AuctionDraftService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionDraftController extends Controller
{
    public function room(AuctionDraftService $service): View
    {
        $draft = AuctionDraft::where('status', 'active')->latest()->first();
        $team = Auth::user()->managedTeam;
        $turnTimeoutSeconds = $service->turnTimeoutSeconds();

        return view('manager.auction-draft', compact('draft', 'team', 'turnTimeoutSeconds'));
    }

    public function spin(AuctionDraft $draft, AuctionDraftService $service): JsonResponse
    {
        $team = $this->requireTeam();
        if (! $team) {
            return response()->json(['error' => 'You are not assigned to manage a team.'], 422);
        }

        $result = $service->spinCountry($draft, $team->id);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json($result);
    }

    public function nominate(Request $request, AuctionDraft $draft, AuctionDraftService $service): JsonResponse
    {
        $team = $this->requireTeam();
        if (! $team) {
            return response()->json(['error' => 'You are not assigned to manage a team.'], 422);
        }

        $validated = $request->validate(['player_id' => ['required', 'exists:players,id']]);

        $result = $service->nominate($draft, $team->id, (int) $validated['player_id']);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json(['success' => true, 'session_id' => $result['session']->id]);
    }

    public function skip(AuctionDraft $draft, AuctionDraftService $service): JsonResponse
    {
        $team = $this->requireTeam();
        if (! $team) {
            return response()->json(['error' => 'You are not assigned to manage a team.'], 422);
        }

        $result = $service->skipTurn($draft, $team->id);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json($result);
    }

    public function rejoin(AuctionDraft $draft, AuctionDraftService $service): JsonResponse
    {
        $team = $this->requireTeam();
        if (! $team) {
            return response()->json(['error' => 'You are not assigned to manage a team.'], 422);
        }

        $result = $service->rejoin($draft, $team->id);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json($result);
    }

    /**
     * Polled by the draft room: draft-level state (whose turn, country,
     * burned list, players available to pick next) plus a look at the
     * auction pool building up behind the scenes. Picking here never
     * opens a live bid — that's a separate Auction phase the admin
     * runs later — so there's no "active session" concept anymore.
     */
    public function state(AuctionDraft $draft, AuctionDraftService $draftService): JsonResponse
    {
        $draft = $draftService->autoAdvanceIfExpired($draft);
        $team = Auth::user()->managedTeam;

        $teams = Team::whereIn('id', $draft->turn_order)->with('manager')->get()->keyBy('id');
        $order = collect($draft->turn_order)->map(fn ($id) => $teams->get($id))->filter()->values();

        $shortlistedIds = $team ? $team->shortlistedPlayers()->pluck('players.id')->all() : [];

        $availablePlayers = [];
        if ($draft->current_country) {
            $availablePlayers = Player::query()
                ->transferable()
                ->notNominated()
                ->whereNull('team_id')
                ->where('is_active', true)
                ->where('country', $draft->current_country)
                ->orderByDesc('current_value')
                ->get(['id', 'name', 'role', 'tier', 'base_value', 'image'])
                ->map(fn (Player $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'role' => $p->role,
                    'tier' => $p->tier,
                    'base_value' => (float) $p->base_value,
                    'image' => $p->image ? asset('storage/' . $p->image) : null,
                    'is_shortlisted' => in_array($p->id, $shortlistedIds, true),
                ]);
        }

        $pool = AuctionSession::with(['player', 'nominatedBy'])
            ->where('auction_draft_id', $draft->id)
            ->where('status', 'scheduled')
            ->get()
            ->sortBy([
                fn ($a, $b) => array_search($a->tier, AuctionSession::TIER_ORDER) <=> array_search($b->tier, AuctionSession::TIER_ORDER),
                fn ($a, $b) => (float) $b->starting_bid <=> (float) $a->starting_bid,
            ])
            ->values()
            ->map(fn (AuctionSession $s) => [
                'id' => $s->id,
                'player_name' => $s->player?->name,
                'tier' => $s->tier,
                'country' => $s->player?->country,
                'base_value' => (float) $s->starting_bid,
                'nominated_by' => $s->nominatedBy?->short_name ?? $s->nominatedBy?->name,
            ]);

        $shortlist = $team
            ? $team->shortlistedPlayers()
                ->whereNull('players.team_id')
                ->where('players.is_active', true)
                ->orderBy('players.name')
                ->get(['players.id', 'players.name', 'players.country', 'players.role', 'players.tier', 'players.base_value'])
                ->map(fn (Player $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'country' => $p->country,
                    'role' => $p->role,
                    'tier' => $p->tier,
                    'base_value' => (float) $p->base_value,
                    'is_current_country' => $p->country === $draft->current_country,
                ])
            : [];

        $passedTeamIds = $draft->passedTeamIds();

        return response()->json([
            'shortlist' => $shortlist,
            'status' => $draft->status,
            'current_country' => $draft->current_country,
            'burned_countries' => $draft->burned_countries ?? [],
            'country_picker' => $this->teamPayload($teams->get($draft->countryPickerTeamId())),
            'active_picker' => $this->teamPayload($teams->get($draft->activePickerTeamId())),
            'turn_order' => $order->map(fn (Team $t) => $this->teamPayload($t)),
            'is_your_turn_to_spin' => $team && $draft->current_country === null && $draft->countryPickerTeamId() === $team->id,
            'is_your_turn_to_pick' => $team && $draft->current_country !== null && $draft->activePickerTeamId() === $team->id,
            'turn_deadline_at' => $draft->status === 'active' ? $draft->turn_deadline_at?->toIso8601String() : null,
            'passed_team_ids' => $passedTeamIds,
            'passed_teams' => collect($passedTeamIds)->map(fn ($id) => $this->teamPayload($teams->get($id)))->filter()->values(),
            'you_have_passed' => $team && $draft->hasTeamPassed($team->id),
            'available_players' => $availablePlayers,
            'pool' => $pool,
            'pool_count' => $pool->count(),
        ]);
    }

    private function teamPayload(?Team $team): ?array
    {
        if (! $team) {
            return null;
        }

        return [
            'team_id' => $team->id,
            'team_name' => $team->name,
            'team_short_name' => $team->short_name,
            'team_logo' => $team->logo ? asset('storage/' . $team->logo) : null,
            'manager_name' => $team->manager?->name,
            'manager_avatar' => $team->manager?->avatar ? asset('storage/' . $team->manager->avatar) : null,
        ];
    }

    private function requireTeam(): ?Team
    {
        return Auth::user()->managedTeam;
    }
}
