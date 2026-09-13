<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Contracts\View\View;

/**
 * Admin oversight of manager-run series/tri-series. Full management
 * (respond, add fixtures, cancel) happens through the same
 * manager.series.* routes — an admin is authorized there regardless
 * of which teams are actually involved — this is just the league-wide
 * list for visibility.
 */
class SeriesController extends Controller
{
    public function index(): View
    {
        $series = Series::with(['teams', 'createdByTeam'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.series.index', compact('series'));
    }
}
