<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\Competition;

/**
 * Drives the fixed 4-team playoff bracket (ESL house format):
 *   Playoff 1: seed 1 v seed 2 -> winner goes straight to the Final
 *   Playoff 2: seed 3 v seed 4 -> winner advances, loser is out
 *   Qualifier: loser of Playoff 1 v winner of Playoff 2
 *   Final:     winner of Playoff 1 v winner of the Qualifier
 *
 * Playoff 1 & 2 are created together, manually, once the league stage
 * is done (generatePlayoffs). The Qualifier and Final are then created
 * automatically as their prerequisite results come in — see
 * onMatchConfirmed(), called from AdminMatchController::confirm().
 */
class CompetitionBracketService
{
    public function __construct(private readonly CompetitionStandingsService $standings)
    {
    }

    /**
     * @return array{error?: string, playoff1?: CricketMatch, playoff2?: CricketMatch}
     */
    public function generatePlayoffs(Competition $competition): array
    {
        if (! $competition->has_playoffs) {
            return ['error' => 'This competition does not use a playoff stage.'];
        }

        if ($competition->playoffMatches()->exists()) {
            return ['error' => 'Playoffs have already been generated for this competition.'];
        }

        $table = $this->standings->standings($competition);

        if ($table->count() < 4) {
            return ['error' => 'At least 4 teams are needed in the standings to generate playoffs.'];
        }

        $top4 = $table->take(4)->values();
        $matchDate = now()->addWeek();

        $playoff1 = CricketMatch::create([
            'competition_id' => $competition->id,
            'stage' => Competition::STAGE_PLAYOFF_1,
            'home_team_id' => $top4[0]['team']->id,
            'away_team_id' => $top4[1]['team']->id,
            'match_date' => $matchDate,
            'status' => 'pending_review',
            'summary_notes' => 'Playoff 1 — 1st seed v 2nd seed. Winner advances directly to the Final.',
        ]);

        $playoff2 = CricketMatch::create([
            'competition_id' => $competition->id,
            'stage' => Competition::STAGE_PLAYOFF_2,
            'home_team_id' => $top4[2]['team']->id,
            'away_team_id' => $top4[3]['team']->id,
            'match_date' => $matchDate->copy()->addDay(),
            'status' => 'pending_review',
            'summary_notes' => 'Playoff 2 — 3rd seed v 4th seed. Winner faces the Playoff 1 loser in the Qualifier.',
        ]);

        $competition->update(['status' => 'playoffs']);

        return ['playoff1' => $playoff1, 'playoff2' => $playoff2];
    }

    /**
     * Call after any competition match is marked 'confirmed' — advances
     * the bracket if that result unlocks the next stage. Safe to call
     * on every confirmation; it's a no-op unless the conditions match.
     */
    public function onMatchConfirmed(CricketMatch $match): void
    {
        if (! $match->competition_id) {
            return;
        }

        $competition = $match->competition;

        if ($match->stage === Competition::STAGE_FINAL) {
            $competition->update(['status' => 'completed']);

            return;
        }

        if ($match->stage === Competition::STAGE_QUALIFIER_2) {
            $this->maybeCreateFinal($competition);

            return;
        }

        if (in_array($match->stage, [Competition::STAGE_PLAYOFF_1, Competition::STAGE_PLAYOFF_2], true)) {
            $this->maybeCreateQualifier($competition);
        }
    }

    private function maybeCreateQualifier(Competition $competition): void
    {
        if ($competition->matches()->where('stage', Competition::STAGE_QUALIFIER_2)->exists()) {
            return;
        }

        $playoff1 = $competition->matches()->where('stage', Competition::STAGE_PLAYOFF_1)->first();
        $playoff2 = $competition->matches()->where('stage', Competition::STAGE_PLAYOFF_2)->first();

        if (! $playoff1 || $playoff1->status !== 'confirmed' || ! $playoff1->winner_team_id) {
            return;
        }

        if (! $playoff2 || $playoff2->status !== 'confirmed' || ! $playoff2->winner_team_id) {
            return;
        }

        $playoff1Loser = $playoff1->winner_team_id === $playoff1->home_team_id
            ? $playoff1->away_team_id
            : $playoff1->home_team_id;

        CricketMatch::create([
            'competition_id' => $competition->id,
            'stage' => Competition::STAGE_QUALIFIER_2,
            'home_team_id' => $playoff1Loser,
            'away_team_id' => $playoff2->winner_team_id,
            'match_date' => now()->addWeeks(2),
            'status' => 'pending_review',
            'summary_notes' => 'Qualifier — Playoff 1 loser v Playoff 2 winner. Winner meets the Playoff 1 winner in the Final.',
        ]);
    }

    private function maybeCreateFinal(Competition $competition): void
    {
        if ($competition->matches()->where('stage', Competition::STAGE_FINAL)->exists()) {
            return;
        }

        $playoff1 = $competition->matches()->where('stage', Competition::STAGE_PLAYOFF_1)->first();
        $qualifier = $competition->matches()->where('stage', Competition::STAGE_QUALIFIER_2)->first();

        if (! $playoff1 || ! $playoff1->winner_team_id) {
            return;
        }

        if (! $qualifier || $qualifier->status !== 'confirmed' || ! $qualifier->winner_team_id) {
            return;
        }

        CricketMatch::create([
            'competition_id' => $competition->id,
            'stage' => Competition::STAGE_FINAL,
            'home_team_id' => $playoff1->winner_team_id,
            'away_team_id' => $qualifier->winner_team_id,
            'match_date' => now()->addWeeks(3),
            'status' => 'pending_review',
            'summary_notes' => 'The Final — Playoff 1 winner v Qualifier winner.',
        ]);
    }
}
