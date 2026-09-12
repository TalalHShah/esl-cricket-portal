<?php

namespace App\Console\Commands;

use App\Models\CricketMatch;
use App\Services\PlayerValuationService;
use Illuminate\Console\Command;

class ApplyPlayerValuations extends Command
{
    protected $signature = 'valuations:apply {match_id? : The ID of the match to apply valuations for}';
    protected $description = 'Apply player valuations based on match outcomes';

    public function handle(): int
    {
        $matchId = $this->argument('match_id');

        if ($matchId) {
            $match = CricketMatch::find($matchId);
            if (!$match) {
                $this->error("Match $matchId not found.");
                return 1;
            }

            $service = new PlayerValuationService();
            $service->updateValuationsForMatch($match);
            $this->info("Valuations applied for match $matchId.");
            return 0;
        }

        // Apply to all confirmed matches without valuations applied yet
        $matches = CricketMatch::where('status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($matches->isEmpty()) {
            $this->info('No confirmed matches to process.');
            return 0;
        }

        $service = new PlayerValuationService();
        foreach ($matches as $match) {
            $service->updateValuationsForMatch($match);
            $this->line("✓ Applied valuations for match: {$match->homeTeam?->name} vs {$match->awayTeam?->name}");
        }

        $this->info("Processed " . $matches->count() . " matches.");
        return 0;
    }
}
