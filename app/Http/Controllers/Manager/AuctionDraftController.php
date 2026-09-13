<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\AuctionDraft;
use App\Models\Player;
use App\Models\Team;
use App\Services\AuctionCompletionService;
use App\Services\AuctionDraftService;
use App\Support\AuctionStatePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionDraftController extends Controller
{
    public function room(): View
    {
        $draft = AuctionDraft::where('status', 'active')->latest()->first();
        $team = Auth::user()->managedTeam;

        return view('manager.auction-draft', compact('draft', 'team'));
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

        // Seat the nominator in the new lot's room automatically so
        // they don't have to click "Join" on a player they just picked.
        session(['auction_room_joined_' . $result['session']->id => true]);

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

    /**
     * Polled by the draft room: draft-level state (whose turn, country,
     * burned list) plus, if a lot is currently up for bidding, the
     * players available to nominate next.
     */
    public function state(AuctionDraft $draft, AuctionCompletionService $completion): JsonResponse
    {
        $liveSession = $draft->sessions()->where('status', 'live')->latest()->first();
        if ($liveSession) {
            $completion->completeIfExpired($liveSession);
        }

        $draft->refresh();
        $team = Auth::user()->managedTeam;

        $teams = Team::whereIn('id', $draft->turn_order)->with('manager')->get()->keyBy('id');
        $order = collect($draft->turn_order)->map(fn ($id) => $teams->get($id))->filter()->values();

        $activeSession = $draft->sessions()->whereIn('status', ['live', 'paused'])->latest()->first();

        // Everyone sitting in the merged draft room is automatically
        // seated in whatever lot comes up — no separate "Join Auction
        // Room" click, since they're already present in the one room.
        if ($activeSession && $team && ! session('auction_room_joined_' . $activeSession->id, false)) {
            session(['auction_room_joined_' . $activeSession->id => true]);
        }

        $availablePlayers = [];
        if ($draft->current_country && ! $activeSession) {
            $availablePlayers = Player::query()
                ->transferable()
                ->whereNull('team_id')
                ->where('is_active', true)
                ->where('country', $draft->current_country)
                ->orderByDesc('current_value')
                ->get(['id', 'name', 'role', 'tier', 'base_value'])
                ->map(fn (Player $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'role' => $p->role,
                    'tier' => $p->tier,
                    'base_value' => (float) $p->base_value,
                ]);
        }

        return response()->json([
            'status' => $draft->status,
            'current_country' => $draft->current_country,
            'burned_countries' => $draft->burned_countries ?? [],
            'country_picker' => $this->teamPayload($teams->get($draft->countryPickerTeamId())),
            'active_picker' => $this->teamPayload($teams->get($draft->activePickerTeamId())),
            'turn_order' => $order->map(fn (Team $t) => $this->teamPayload($t)),
            'is_your_turn_to_spin' => $team && $draft->current_country === null && $draft->countryPickerTeamId() === $team->id,
            'is_your_turn_to_pick' => $team && $draft->current_country !== null && $draft->activePickerTeamId() === $team->id && ! $activeSession,
            'available_players' => $availablePlayers,
            'active_session_id' => $activeSession?->id,
            'active_session_status' => $activeSession?->status,
            // The full bidding payload, embedded so the merged draft room
            // never has to redirect to a separate auction room page —
            // one poll, one page, for the whole live event.
            'auction' => $activeSession ? AuctionStatePresenter::present($activeSession, $team) : null,
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
