<?php

namespace App\Http\Controllers\Manager;

use App\Concerns\Sortable;
use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FixtureController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $team = Auth::user()->managedTeam;

        $matches = collect();
        $sort = 'date_desc';

        if ($team) {
            $query = CricketMatch::with(['homeTeam', 'awayTeam'])
                ->where(function ($q) use ($team) {
                    $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id);
                });

            $sort = $this->applySort($query, $request, [
                'date_desc' => fn ($q) => $q->orderByDesc('match_date'),
                'date_asc' => fn ($q) => $q->orderBy('match_date'),
                'status' => fn ($q) => $q->orderBy('status'),
            ], 'date_desc');

            $matches = $query->paginate(10)->withQueryString();
        }

        return view('manager.fixtures', compact('team', 'matches', 'sort'));
    }
}
