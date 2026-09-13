<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\Competition;
use Illuminate\Support\Collection;

/**
 * Computes the league points table live from confirmed match rows —
 * nothing is denormalized/stored, so this is always in sync with the
 * match data. Sorted by points, then Net Run Rate.
 */
class CompetitionStandingsService
{
    /**
     * @return Collection<int, array{
     *   team: \App\Models\Team, played: int, won: int, lost: int, tied: int,
     *   points: int, runs_for: int, overs_for: float, runs_against: int,
     *   overs_against: float, nrr: float
     * }>
     */
    public function standings(Competition $competition): Collection
    {
        $teams = $competition->teams()->get()->keyBy('id');

        $rows = $teams->mapWithKeys(fn ($team) => [$team->id => [
            'team' => $team,
            'played' => 0, 'won' => 0, 'lost' => 0, 'tied' => 0, 'points' => 0,
            'runs_for' => 0, 'overs_for' => 0.0, 'runs_against' => 0, 'overs_against' => 0.0,
            'nrr' => 0.0,
        ]]);

        $matches = $competition->leagueMatches()
            ->where('status', 'confirmed')
            ->get();

        foreach ($matches as $match) {
            $this->applyMatch($rows, $match, $competition);
        }

        return $rows->map(function ($row) {
            $row['nrr'] = $this->netRunRate($row['runs_for'], $row['overs_for'], $row['runs_against'], $row['overs_against']);

            return $row;
        })
            ->sortBy([
                fn ($a, $b) => $b['points'] <=> $a['points'],
                fn ($a, $b) => $b['nrr'] <=> $a['nrr'],
            ])
            ->values();
    }

    private function applyMatch(Collection $rows, CricketMatch $match, Competition $competition): void
    {
        $homeId = $match->home_team_id;
        $awayId = $match->away_team_id;

        if (! $rows->has($homeId) || ! $rows->has($awayId)) {
            return;
        }

        $homeOvers = $match->effectiveOvers('home', $competition->total_overs);
        $awayOvers = $match->effectiveOvers('away', $competition->total_overs);

        $home = $rows[$homeId];
        $away = $rows[$awayId];

        $home['played']++;
        $away['played']++;
        $home['runs_for'] += (int) $match->home_runs;
        $home['overs_for'] += $homeOvers;
        $home['runs_against'] += (int) $match->away_runs;
        $home['overs_against'] += $awayOvers;

        $away['runs_for'] += (int) $match->away_runs;
        $away['overs_for'] += $awayOvers;
        $away['runs_against'] += (int) $match->home_runs;
        $away['overs_against'] += $homeOvers;

        if ($match->winner_team_id === $homeId) {
            $home['won']++;
            $home['points'] += $competition->points_win;
            $away['lost']++;
            $away['points'] += $competition->points_loss;
        } elseif ($match->winner_team_id === $awayId) {
            $away['won']++;
            $away['points'] += $competition->points_win;
            $home['lost']++;
            $home['points'] += $competition->points_loss;
        } else {
            $home['tied']++;
            $away['tied']++;
            $home['points'] += $competition->points_tie;
            $away['points'] += $competition->points_tie;
        }

        $rows[$homeId] = $home;
        $rows[$awayId] = $away;
    }

    private function netRunRate(int $runsFor, float $oversFor, int $runsAgainst, float $oversAgainst): float
    {
        $forRate = $oversFor > 0 ? $runsFor / $oversFor : 0.0;
        $againstRate = $oversAgainst > 0 ? $runsAgainst / $oversAgainst : 0.0;

        return round($forRate - $againstRate, 3);
    }
}
