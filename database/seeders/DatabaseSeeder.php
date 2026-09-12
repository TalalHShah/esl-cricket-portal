<?php

namespace Database\Seeders;

use App\Models\AuctionSession;
use App\Models\CricketMatch;
use App\Models\MatchStat;
use App\Models\NewsArticle;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Team;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ------------------------------------------------------------------
        // Users
        // ------------------------------------------------------------------
        $admin = User::firstOrCreate(
            ['email' => 'admin@esl.test'],
            [
                'name' => 'ESL Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $managerOne = User::firstOrCreate(
            ['email' => 'manager1@esl.test'],
            [
                'name' => 'Adeel Khan',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
            ]
        );

        $managerTwo = User::firstOrCreate(
            ['email' => 'manager2@esl.test'],
            [
                'name' => 'Bilal Ahmed',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
            ]
        );

        $managerThree = User::firstOrCreate(
            ['email' => 'manager3@esl.test'],
            [
                'name' => 'Danish Raza',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
            ]
        );

        // ------------------------------------------------------------------
        // Teams
        // ------------------------------------------------------------------
        $teamsData = [
            [
                'name' => 'Karachi Kings',
                'short_name' => 'KAR',
                'primary_color' => '#0ea5e9',
                'secondary_color' => '#ffffff',
                'manager_id' => $managerOne->id,
                'budget' => 50000000,
                'spent' => 0,
                'description' => 'The coastal powerhouse of the ESL Cricket Portal.',
            ],
            [
                'name' => 'Lahore Lions',
                'short_name' => 'LAH',
                'primary_color' => '#16a34a',
                'secondary_color' => '#ffffff',
                'manager_id' => $managerTwo->id,
                'budget' => 50000000,
                'spent' => 0,
                'description' => 'Heavyweights from the heart of Punjab.',
            ],
            [
                'name' => 'Islamabad United',
                'short_name' => 'ISB',
                'primary_color' => '#dc2626',
                'secondary_color' => '#ffffff',
                'manager_id' => $managerThree->id,
                'budget' => 50000000,
                'spent' => 0,
                'description' => 'Capital side known for aggressive auction bidding.',
            ],
        ];

        $teams = collect($teamsData)->map(function (array $data) {
            return Team::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        });

        $karachi = $teams[0];
        $lahore = $teams[1];
        $islamabad = $teams[2];

        // ------------------------------------------------------------------
        // Players
        // ------------------------------------------------------------------
        $playersData = [
            ['name' => 'Babar Azam', 'country' => 'Pakistan', 'role' => 'Batsman', 'tier' => 'Superstar', 'base_value' => 8000000, 'age' => 30, 'batting_style' => 'Right-hand bat'],
            ['name' => 'Mohammad Rizwan', 'country' => 'Pakistan', 'role' => 'Wicketkeeper', 'tier' => 'Star', 'base_value' => 6000000, 'age' => 32, 'batting_style' => 'Right-hand bat'],
            ['name' => 'Shaheen Afridi', 'country' => 'Pakistan', 'role' => 'Fast Bowler', 'tier' => 'Superstar', 'base_value' => 7500000, 'age' => 25, 'bowling_style' => 'Left-arm fast'],
            ['name' => 'Shadab Khan', 'country' => 'Pakistan', 'role' => 'All-rounder', 'tier' => 'Star', 'base_value' => 5000000, 'age' => 27, 'batting_style' => 'Right-hand bat', 'bowling_style' => 'Leg break'],
            ['name' => 'Fakhar Zaman', 'country' => 'Pakistan', 'role' => 'Batsman', 'tier' => 'Star', 'base_value' => 4500000, 'age' => 34, 'batting_style' => 'Left-hand bat'],
            ['name' => 'Haris Rauf', 'country' => 'Pakistan', 'role' => 'Fast Bowler', 'tier' => 'Star', 'base_value' => 4000000, 'age' => 31, 'bowling_style' => 'Right-arm fast'],
            ['name' => 'Imad Wasim', 'country' => 'Pakistan', 'role' => 'All-rounder', 'tier' => 'Normal', 'base_value' => 2500000, 'age' => 36, 'batting_style' => 'Left-hand bat', 'bowling_style' => 'Slow left-arm orthodox'],
            ['name' => 'Naseem Shah', 'country' => 'Pakistan', 'role' => 'Fast Bowler', 'tier' => 'Star', 'base_value' => 4200000, 'age' => 22, 'bowling_style' => 'Right-arm fast'],
            ['name' => 'Agha Salman', 'country' => 'Pakistan', 'role' => 'All-rounder', 'tier' => 'Normal', 'base_value' => 2000000, 'age' => 31, 'batting_style' => 'Right-hand bat', 'bowling_style' => 'Off break'],
            ['name' => 'Abrar Ahmed', 'country' => 'Pakistan', 'role' => 'Spinner', 'tier' => 'Normal', 'base_value' => 1800000, 'age' => 30, 'bowling_style' => 'Leg break'],
            ['name' => 'Saim Ayub', 'country' => 'Pakistan', 'role' => 'Batsman', 'tier' => 'Star', 'base_value' => 3500000, 'age' => 23, 'batting_style' => 'Left-hand bat'],
            ['name' => 'Azam Khan', 'country' => 'Pakistan', 'role' => 'Wicketkeeper', 'tier' => 'Normal', 'base_value' => 1500000, 'age' => 27, 'batting_style' => 'Right-hand bat'],
            ['name' => 'Mohammad Wasim Jr', 'country' => 'Pakistan', 'role' => 'Fast Bowler', 'tier' => 'Low-value', 'base_value' => 900000, 'age' => 24, 'bowling_style' => 'Right-arm fast-medium'],
            ['name' => 'Usman Khan', 'country' => 'Pakistan', 'role' => 'Batsman', 'tier' => 'Normal', 'base_value' => 1600000, 'age' => 30, 'batting_style' => 'Right-hand bat'],
            ['name' => 'Kamran Ghulam', 'country' => 'Pakistan', 'role' => 'Batsman', 'tier' => 'Low-value', 'base_value' => 800000, 'age' => 30, 'batting_style' => 'Right-hand bat'],
        ];

        $players = collect($playersData)->map(function (array $data) {
            return Player::firstOrCreate(
                ['name' => $data['name'], 'country' => $data['country']],
                array_merge($data, [
                    'current_value' => $data['base_value'],
                    'real_life_form_modifier' => 1.00,
                    'is_auctioned' => false,
                    'is_active' => true,
                ])
            );
        });

        // Assign a few players to teams so the fixture is realistic.
        $karachi->players()->saveMany($players->take(5));
        $lahore->players()->saveMany($players->slice(5, 5));
        $islamabad->players()->saveMany($players->slice(10, 3));

        // Reflect capped spending on the team records.
        $teams->each(function (Team $team) {
            $team->update(['spent' => (float) $team->players()->sum('base_value')]);
        });

        // ------------------------------------------------------------------
        // Matches + Stats
        // ------------------------------------------------------------------
        $matchOne = CricketMatch::firstOrCreate(
            [
                'home_team_id' => $karachi->id,
                'away_team_id' => $lahore->id,
                'match_date' => now()->subDays(7)->startOfHour(),
            ],
            [
                'winner_team_id' => $karachi->id,
                'status' => 'confirmed',
                'submitted_by_user_id' => $managerOne->id,
                'confirmed_by_user_id' => $admin->id,
                'summary_notes' => 'Karachi Kings won by 6 wickets with 8 balls to spare.',
            ]
        );

        $matchTwo = CricketMatch::firstOrCreate(
            [
                'home_team_id' => $lahore->id,
                'away_team_id' => $islamabad->id,
                'match_date' => now()->subDays(3)->startOfHour(),
            ],
            [
                'winner_team_id' => $islamabad->id,
                'status' => 'pending_confirmation',
                'submitted_by_user_id' => $managerThree->id,
                'summary_notes' => 'Awaiting confirmation of the final scorecard.',
            ]
        );

        if ($matchOne->stats()->count() === 0) {
            $karachiPlayers = $karachi->players()->take(3)->get();
            $lahorePlayers = $lahore->players()->take(3)->get();

            foreach ($karachiPlayers as $index => $player) {
                MatchStat::create([
                    'match_id' => $matchOne->id,
                    'player_id' => $player->id,
                    'team_id' => $karachi->id,
                    'runs_scored' => 45 - ($index * 8),
                    'balls_faced' => 34 - ($index * 5),
                    'fours' => 5 - $index,
                    'sixes' => 2 - ($index % 2),
                    'overs_bowled' => 4.0,
                    'maidens' => 0,
                    'runs_conceded' => 22 + ($index * 3),
                    'wickets_taken' => 2 - ($index % 2),
                    'catches' => $index,
                    'stumpings' => 0,
                    'was_exceptional' => $index === 0,
                    'value_change' => 500000 - ($index * 100000),
                ]);
            }

            foreach ($lahorePlayers as $index => $player) {
                MatchStat::create([
                    'match_id' => $matchOne->id,
                    'player_id' => $player->id,
                    'team_id' => $lahore->id,
                    'runs_scored' => 30 - ($index * 6),
                    'balls_faced' => 28 - ($index * 4),
                    'fours' => 3 - $index,
                    'sixes' => 1,
                    'overs_bowled' => 3.0,
                    'maidens' => 0,
                    'runs_conceded' => 28 + ($index * 4),
                    'wickets_taken' => 1,
                    'catches' => 1,
                    'stumpings' => $index === 0 ? 1 : 0,
                    'was_exceptional' => false,
                    'value_change' => -200000 + ($index * 100000),
                ]);
            }
        }

        // ------------------------------------------------------------------
        // Transfers
        // ------------------------------------------------------------------
        $transferPlayer = $players->firstWhere('name', 'Saim Ayub');

        if ($transferPlayer) {
            Transfer::firstOrCreate(
                [
                    'player_id' => $transferPlayer->id,
                    'to_team_id' => $karachi->id,
                ],
                [
                    'from_team_id' => $islamabad->id,
                    'fee' => 3500000,
                    'type' => 'direct',
                    'status' => 'approved',
                    'requested_by_user_id' => $managerOne->id,
                    'approved_by_user_id' => $admin->id,
                    'effective_at' => now()->subDays(1),
                    'notes' => 'Season-long loan converted to permanent move.',
                ]
            );
        }

        // ------------------------------------------------------------------
        // News Articles
        // ------------------------------------------------------------------
        NewsArticle::firstOrCreate(
            ['slug' => 'kings-crown-opening-night'],
            [
                'title' => 'Kings Crown the Opening Night',
                'excerpt' => 'Karachi Kings chase down 168 with clinical ease in the ESL season opener.',
                'body' => 'Karachi Kings began their ESL campaign with a commanding six-wicket victory over Lahore Lions. A composed middle-order chase, anchored by an exceptional half-century, saw the hosts home with eight balls remaining. Lahore will rue a middle-order collapse that cost them momentum at the death.',
                'category' => 'match_report',
                'status' => 'published',
                'author_id' => $admin->id,
                'is_featured' => true,
                'views' => 128,
                'published_at' => now()->subDays(6),
            ]
        );

        NewsArticle::firstOrCreate(
            ['slug' => 'auction-window-preview'],
            [
                'title' => 'Auction Window Preview: Superstars on the Block',
                'excerpt' => 'All eyes turn to the bidding table as franchises prepare for a frantic auction.',
                'body' => 'With budgets locked and squads taking shape, the upcoming ESL auction promises fireworks. Superstar tier players headline the pool, while several franchises are set to battle for value signings in the lower tiers.',
                'category' => 'preview',
                'status' => 'draft',
                'author_id' => $admin->id,
                'is_featured' => false,
                'views' => 0,
                'published_at' => null,
            ]
        );

        // ------------------------------------------------------------------
        // Auction Sessions
        // ------------------------------------------------------------------
        AuctionSession::firstOrCreate(
            ['name' => 'ESL Mega Auction 2025'],
            [
                'description' => 'The flagship auction session of the ESL Cricket Portal season.',
                'status' => 'scheduled',
                'player_id' => $players->first()->id,
                'starting_bid' => 8000000,
                'current_bid' => 0,
                'bid_increment' => 100000,
                'started_by_user_id' => $admin->id,
            ]
        );

        // ------------------------------------------------------------------
        // Settings
        // ------------------------------------------------------------------
        $settings = [
            ['key' => 'platform.name', 'value' => 'ESL Cricket Portal', 'type' => 'string', 'group' => 'general', 'description' => 'Public platform name.', 'is_public' => true],
            ['key' => 'platform.tagline', 'value' => 'Where every run counts.', 'type' => 'string', 'group' => 'general', 'description' => 'Homepage tagline.', 'is_public' => true],
            ['key' => 'auction.default_bid_increment', 'value' => '100000', 'type' => 'decimal', 'group' => 'auction', 'description' => 'Default bid increment amount.', 'is_public' => false],
            ['key' => 'auction.min_squad_size', 'value' => '11', 'type' => 'integer', 'group' => 'auction', 'description' => 'Minimum squad size per team.', 'is_public' => false],
            ['key' => 'auction.max_squad_size', 'value' => '20', 'type' => 'integer', 'group' => 'auction', 'description' => 'Maximum squad size per team.', 'is_public' => false],
            ['key' => 'match.require_admin_confirmation', 'value' => 'true', 'type' => 'boolean', 'group' => 'matches', 'description' => 'Require admin confirmation before a match is finalised.', 'is_public' => false],
            ['key' => 'match.allow_pending_review', 'value' => 'true', 'type' => 'boolean', 'group' => 'matches', 'description' => 'Allow managers to submit matches for review.', 'is_public' => true],
            ['key' => 'news.featured_limit', 'value' => '3', 'type' => 'integer', 'group' => 'news', 'description' => 'Number of featured articles on the homepage.', 'is_public' => true],
            ['key' => 'notifications.channels', 'value' => json_encode(['database', 'broadcast']), 'type' => 'json', 'group' => 'notifications', 'description' => 'Enabled notification channels.', 'is_public' => false],
            ['key' => 'platform.default_team_budget', 'value' => '50000000', 'type' => 'decimal', 'group' => 'auction', 'description' => 'Default starting budget for new teams.', 'is_public' => true],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                array_merge($setting, ['updated_by_user_id' => $admin->id])
            );
        }
    }
}
