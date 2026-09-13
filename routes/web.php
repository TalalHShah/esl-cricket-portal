<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AuctionSessionController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\AdminTeamController;
use App\Http\Controllers\Admin\AdminPlayerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AdminMatchController;
use App\Http\Controllers\Admin\AdminNewsController;
use App\Http\Controllers\Admin\AuctionDraftController as AdminAuctionDraftController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Manager\AuctionController;
use App\Http\Controllers\Manager\AuctionDraftController;
use App\Http\Controllers\Manager\CallController;
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
Route::get('/news/{newsArticle}', [NewsController::class, 'show'])->name('news.show');

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
    Route::post('/transfers/offers/{transfer}/approve', [TransferMarketController::class, 'approveOffer'])->name('transfers.offers.approve');
    Route::post('/transfers/offers/{transfer}/reject', [TransferMarketController::class, 'rejectOffer'])->name('transfers.offers.reject');

    Route::get('/auction', [AuctionController::class, 'index'])->name('auction');
    Route::get('/auction/{auctionSession}/room', [AuctionController::class, 'room'])->name('auction.room');
    Route::post('/auction/{auctionSession}/join', [AuctionController::class, 'join'])->name('auction.join');
    Route::post('/auction/{auctionSession}/bid', [AuctionController::class, 'bid'])->name('auction.bid');
    Route::post('/auction/{auctionSession}/pass', [AuctionController::class, 'pass'])->name('auction.pass');
    Route::get('/auction/{auctionSession}/state', [AuctionController::class, 'state'])->name('auction.state');

    Route::get('/draft', [AuctionDraftController::class, 'room'])->name('draft');
    Route::get('/draft/{draft}/state', [AuctionDraftController::class, 'state'])->name('draft.state');
    Route::post('/draft/{draft}/spin', [AuctionDraftController::class, 'spin'])->name('draft.spin');
    Route::post('/draft/{draft}/nominate', [AuctionDraftController::class, 'nominate'])->name('draft.nominate');
    Route::post('/draft/{draft}/skip', [AuctionDraftController::class, 'skip'])->name('draft.skip');
    Route::get('/call/{roomKey}', [CallController::class, 'show'])->name('call');

    Route::get('/scouts', [ScoutController::class, 'index'])->name('scouts');
    Route::post('/scouts/{player}/sign', [ScoutController::class, 'sign'])->name('scouts.sign');

    Route::get('/fixtures', [ManagerFixtureController::class, 'index'])->name('fixtures');

    Route::get('/livestream', [LiveStreamController::class, 'index'])->name('livestream');

    Route::get('/team', [ManagerTeamController::class, 'show'])->name('team');
    Route::post('/team/logo', [ManagerTeamController::class, 'updateLogo'])->name('team.logo.update');

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
    Route::resource('managers', ManagerController::class)->except(['show']);

    // Team CRUD
    Route::resource('teams', AdminTeamController::class)->except(['show']);

    // Player CRUD
    Route::resource('players', AdminPlayerController::class)->except(['show']);

    // Match CRUD + result confirmation
    Route::get('matches', [AdminMatchController::class, 'index'])->name('matches.index');
    Route::get('matches/create', [AdminMatchController::class, 'create'])->name('matches.create');
    Route::post('matches', [AdminMatchController::class, 'store'])->name('matches.store');
    Route::get('matches/{match}/edit', [AdminMatchController::class, 'edit'])->name('matches.edit');
    Route::put('matches/{match}', [AdminMatchController::class, 'update'])->name('matches.update');
    Route::post('matches/{match}/confirm', [AdminMatchController::class, 'confirm'])->name('matches.confirm');
    Route::delete('matches/{match}', [AdminMatchController::class, 'destroy'])->name('matches.destroy');
    Route::post('matches/{match}/screenshots', [AdminMatchController::class, 'uploadScreenshot'])->name('matches.screenshots.store');
    Route::delete('matches/{match}/screenshots/{screenshot}', [AdminMatchController::class, 'destroyScreenshot'])->name('matches.screenshots.destroy');

    // News CRUD
    Route::get('news', [AdminNewsController::class, 'index'])->name('news.index');
    Route::get('news/create', [AdminNewsController::class, 'create'])->name('news.create');
    Route::post('news', [AdminNewsController::class, 'store'])->name('news.store');
    Route::get('news/{newsArticle}/edit', [AdminNewsController::class, 'edit'])->name('news.edit');
    Route::put('news/{newsArticle}', [AdminNewsController::class, 'update'])->name('news.update');
    Route::delete('news/{newsArticle}', [AdminNewsController::class, 'destroy'])->name('news.destroy');

    // Auction Session management
    Route::get('auctions', [AuctionSessionController::class, 'index'])->name('auctions.index');
    Route::get('auctions/create', [AuctionSessionController::class, 'create'])->name('auctions.create');
    Route::post('auctions', [AuctionSessionController::class, 'store'])->name('auctions.store');
    Route::post('auctions/{auction}/start', [AuctionSessionController::class, 'start'])->name('auctions.start');
    Route::post('auctions/{auction}/pause', [AuctionSessionController::class, 'pause'])->name('auctions.pause');
    Route::post('auctions/{auction}/resume', [AuctionSessionController::class, 'resume'])->name('auctions.resume');
    Route::post('auctions/{auction}/complete', [AuctionSessionController::class, 'complete'])->name('auctions.complete');
    Route::post('auctions/{auction}/cancel', [AuctionSessionController::class, 'cancel'])->name('auctions.cancel');

    // Country draft
    Route::get('draft/create', [AdminAuctionDraftController::class, 'create'])->name('draft.create');
    Route::post('draft', [AdminAuctionDraftController::class, 'store'])->name('draft.store');
    Route::post('draft/{draft}/cancel', [AdminAuctionDraftController::class, 'cancel'])->name('draft.cancel');
    Route::delete('auctions/{auction}', [AuctionSessionController::class, 'destroy'])->name('auctions.destroy');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/transfer-window/toggle', [SettingController::class, 'toggleTransferWindow'])->name('settings.transfer-window.toggle');
});
