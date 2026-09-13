<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\Competition;
use Illuminate\Support\Carbon;

/**
 * Generates the round-robin league-stage fixture list for a
 * competition using the standard "circle" scheduling method, then
 * (for a double round-robin) mirrors it with home/away swapped.
 */
class CompetitionFixtureService
{
    public function generateLeagueFixtures(Competition $competition): int
    {
        $teamIds = $competition->teams()->pluck('teams.id')->all();

        if (count($teamIds) < 2) {
            throw new \InvalidArgumentException('At least 2 teams are required to generate fixtures.');
        }

        // Wipe any previously generated (but not yet played) league
        // fixtures so re-generating after changing the roster doesn't
        // leave stale matches behind.
        $competition->leagueMatches()->where('status', 'pending_review')->delete();

        $pairings = $this->circleMethodPairings($teamIds);

        $fixtures = [];
        foreach ($pairings as [$home, $away]) {
            $fixtures[] = ['home' => $home, 'away' => $away, 'leg' => 1];
        }

        if ((int) $competition->rounds >= 2) {
            foreach ($pairings as [$home, $away]) {
                $fixtures[] = ['home' => $away, 'away' => $home, 'leg' => 2];
            }
        }

        $date = $competition->starts_on ? Carbon::parse($competition->starts_on) : now()->addWeek()->startOfDay();
        $interval = max(1, (int) $competition->fixture_interval_days);

        foreach ($fixtures as $i => $fixture) {
            CricketMatch::create([
                'competition_id' => $competition->id,
                'stage' => Competition::STAGE_LEAGUE,
                'leg' => $fixture['leg'],
                'home_team_id' => $fixture['home'],
                'away_team_id' => $fixture['away'],
                'match_date' => $date->copy()->addDays($i * $interval),
                'status' => 'pending_review',
            ]);
        }

        if ($competition->status === 'draft') {
            $competition->update(['status' => 'league_stage']);
        }

        return count($fixtures);
    }

    /**
     * Single round-robin pairings via the circle method: fix one team,
     * rotate the rest. Returns a flat list of [home_id, away_id] pairs
     * covering every team playing every other team exactly once.
     */
    private function circleMethodPairings(array $teamIds): array
    {
        $teams = array_values($teamIds);

        if (count($teams) % 2 !== 0) {
            $teams[] = null; // bye
        }

        $n = count($teams);
        $rounds = $n - 1;
        $half = intdiv($n, 2);
        $pairings = [];

        for ($round = 0; $round < $rounds; $round++) {
            for ($i = 0; $i < $half; $i++) {
                $home = $teams[$i];
                $away = $teams[$n - 1 - $i];

                if ($home !== null && $away !== null) {
                    // Alternate which side is "home" round to round so
                    // one team doesn't get stuck away every time.
                    $pairings[] = $round % 2 === 0 ? [$home, $away] : [$away, $home];
                }
            }

            // Rotate everyone except the first fixed team.
            $fixed = $teams[0];
            $rest = array_slice($teams, 1);
            array_unshift($rest, array_pop($rest));
            $teams = array_merge([$fixed], $rest);
        }

        return $pairings;
    }
}
