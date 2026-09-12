<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\AdminTeamController;
use App\Http\Controllers\Admin\AdminPlayerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PlayerController;
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

// Public Admin Dashboard (read-only for all)
Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

// Admin Control Panel (CRUD) — TODO: add IsAdmin middleware after auth is built
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/panel', [AdminController::class, 'panel'])->name('panel');

    // Manager CRUD
    Route::resource('managers', ManagerController::class);

    // Team CRUD
    Route::resource('teams', AdminTeamController::class);

    // Player CRUD
    Route::resource('players', AdminPlayerController::class);

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});
