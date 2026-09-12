<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Contracts\View\View;

class PublicManagerController extends Controller
{
    /**
     * Public-facing list of team managers — no financial or internal data.
     */
    public function index(): View
    {
        $teams = Team::with('manager')
            ->withCount('players')
            ->orderBy('name')
            ->get();

        return view('managers.index', compact('teams'));
    }
}
