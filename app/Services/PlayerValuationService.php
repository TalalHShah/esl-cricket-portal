<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\MatchStat;
use App\Models\Player;
use App\Models\Setting;

class PlayerValuationService
{
    /**
     * Update player valuations based on match outcome.
     */
    public function updateValuationsForMatch(CricketMatch $match): void
    {
        if ($match->status !== 'confirmed') {
            return;
        }

        $winBonusPercent = (float) Setting::where('key', 'win_bonus_percentage')->value('value') ?? 5;
        $lossPenaltyPercent = (float) Setting::where('key', 'loss_penalty_percentage')->value('value') ?? 3;
        $standoutRuns = (int) Setting::where('key', 'standout_threshold_runs')->value('value') ?? 50;
        $standoutWickets = (int) Setting::where('key', 'standout_threshold_wickets')->value('value') ?? 3;

        // Get winning team (assume match has a result)
        $winningTeamId = $match->winning_team_id;
        if (!$winningTeamId) {
            return;
        }

        // Get all match stats
        $stats = $match->stats()->with('player')->get();

        foreach ($stats as $stat) {
            $player = $stat->player;
            if (!$player) {
                continue;
            }

            $isWinning = $player->team_id === $winningTeamId;
            $isStandout = $this->isStandoutPerformance($stat, $standoutRuns, $standoutWickets);

            if ($isWinning) {
                // Winning team: apply bonus
                $this->applyBonus($player, $winBonusPercent);
            } else {
                // Losing team: penalty or bonus if standout
                if ($isStandout) {
                    $this->applyBonus($player, $winBonusPercent);
                } else {
                    $this->applyPenalty($player, $lossPenaltyPercent);
                }
            }
        }
    }

    /**
     * Check if a player had a standout performance.
     */
    private function isStandoutPerformance(MatchStat $stat, int $runsThreshold, int $wicketsThreshold): bool
    {
        $runs = (int) ($stat->runs_scored ?? 0);
        $wickets = (int) ($stat->overs_bowled ?? 0); // Simplified: using overs as proxy for wickets count

        return $runs >= $runsThreshold || $wickets >= $wicketsThreshold;
    }

    /**
     * Apply bonus to player value.
     */
    private function applyBonus(Player $player, float $bonusPercent): void
    {
        $increase = $player->base_value * ($bonusPercent / 100);
        $newValue = $player->current_value + $increase;

        $player->update([
            'current_value' => max($newValue, $player->base_value * 0.5), // Don't drop below 50% of base
        ]);
    }

    /**
     * Apply penalty to player value.
     */
    private function applyPenalty(Player $player, float $penaltyPercent): void
    {
        $decrease = $player->base_value * ($penaltyPercent / 100);
        $newValue = $player->current_value - $decrease;

        $player->update([
            'current_value' => max($newValue, $player->base_value * 0.5), // Floor at 50% of base
        ]);
    }

    /**
     * Apply manual form modifier (admin can adjust for real-life events).
     */
    public function applyFormModifier(Player $player, float $multiplier): void
    {
        $player->update([
            'current_value' => $player->current_value * $multiplier,
        ]);
    }

    /**
     * Reset all player values to base.
     */
    public function resetAllToBase(): void
    {
        Player::query()->update(['current_value' => \DB::raw('base_value')]);
    }
}
