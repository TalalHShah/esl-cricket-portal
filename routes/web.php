<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AuctionSessionController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\AdminTeamController;
use App\Http\Controllers\Admin\AdminPlayerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Manager\AuctionController;
use App\Http\Controllers\Manager\FixtureController as ManagerFixtureController;
use App\Http\Controllers\Manager\LeagueTeamController;
use App\Http\Controllers\Manager\LiveStreamController;
use App\Http\Controllers\Manager\PlayerController as ManagerPlayerController;
use App\Http\Controllers\Manager\ProfileController;
use App\Http\Controllers\Manager\ScoutController;
use App\Http\Controllers\Manager\TeamController as ManagerTeamController;
use App\Http\Controllers\Manager\TransferMarketController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PublicManagerController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard (homepage)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Teams
Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');

// Managers (public listing)
Route::get('/managers', [PublicManagerController::class, 'index'])->name('managers.index');

// Players
Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');

// Matches
Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');

// News
Route::get('/news', [NewsController::class, 'index'])->name('news.index');

// Transfers
Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');

// Valuations
Route::get('/valuations/league', [\App\Http\Controllers\ValuationController::class, 'league'])->name('valuations.league');
Route::get('/valuations/team/{team}', [\App\Http\Controllers\ValuationController::class, 'byTeam'])->name('valuations.team');
Route::get('/valuations/tiers', [\App\Http\Controllers\ValuationController::class, 'tiers'])->name('valuations.tiers');

// Authentication
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Manager Portal (auth required)
Route::middleware('manager')->prefix('manager')->name('manager.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Manager\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/transfers', [TransferMarketController::class, 'index'])->name('transfers');
    Route::post('/transfers/{player}/offer', [TransferMarketController::class, 'makeOffer'])->name('transfers.offer');

    Route::get('/auction', [AuctionController::class, 'index'])->name('auction');
    Route::get('/auction/{auctionSession}/room', [AuctionController::class, 'room'])->name('auction.room');
    Route::post('/auction/{auctionSession}/join', [AuctionController::class, 'join'])->name('auction.join');
    Route::post('/auction/{auctionSession}/bid', [AuctionController::class, 'bid'])->name('auction.bid');

    Route::get('/scouts', [ScoutController::class, 'index'])->name('scouts');
    Route::post('/scouts/{player}/sign', [ScoutController::class, 'sign'])->name('scouts.sign');

    Route::get('/fixtures', [ManagerFixtureController::class, 'index'])->name('fixtures');

    Route::get('/livestream', [LiveStreamController::class, 'index'])->name('livestream');

    Route::get('/team', [ManagerTeamController::class, 'show'])->name('team');

    Route::get('/teams', [LeagueTeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/{team}', [LeagueTeamController::class, 'show'])->name('teams.show');

    Route::get('/players/{player}', [ManagerPlayerController::class, 'show'])->name('players.show');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Admin Control Panel (CRUD) — restricted to authenticated admins only
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/panel', [AdminController::class, 'panel'])->name('panel');

    // Manager CRUD
    Route::resource('managers', ManagerController::class);

    // Team CRUD
    Route::resource('teams', AdminTeamController::class);

    // Player CRUD
    Route::resource('players', AdminPlayerController::class);

    // Auction Session management
    Route::get('auctions', [AuctionSessionController::class, 'index'])->name('auctions.index');
    Route::get('auctions/create', [AuctionSessionController::class, 'create'])->name('auctions.create');
    Route::post('auctions', [AuctionSessionController::class, 'store'])->name('auctions.store');
    Route::post('auctions/{auction}/start', [AuctionSessionController::class, 'start'])->name('auctions.start');
    Route::post('auctions/{auction}/pause', [AuctionSessionController::class, 'pause'])->name('auctions.pause');
    Route::post('auctions/{auction}/resume', [AuctionSessionController::class, 'resume'])->name('auctions.resume');
    Route::post('auctions/{auction}/complete', [AuctionSessionController::class, 'complete'])->name('auctions.complete');
    Route::post('auctions/{auction}/cancel', [AuctionSessionController::class, 'cancel'])->name('auctions.cancel');
    Route::delete('auctions/{auction}', [AuctionSessionController::class, 'destroy'])->name('auctions.destroy');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/transfer-window/toggle', [SettingController::class, 'toggleTransferWindow'])->name('settings.transfer-window.toggle');
});
