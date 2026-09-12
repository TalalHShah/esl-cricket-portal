<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionDraft;
use App\Models\Team;
use App\Services\AuctionDraftService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuctionDraftController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (AuctionDraft::where('status', 'active')->exists()) {
            return redirect()->route('manager.draft')->with('status', 'A draft is already in progress.');
        }

        $teams = Team::orderBy('name')->get();

        return view('admin.auctions.draft-create', compact('teams'));
    }

    public function store(Request $request, AuctionDraftService $service): RedirectResponse
    {
        if (AuctionDraft::where('status', 'active')->exists()) {
            return back()->withErrors(['draft' => 'A draft is already in progress.']);
        }

        $validated = $request->validate([
            'team_order' => ['required', 'array', 'min:2'],
            'team_order.*' => ['required', 'exists:teams,id', 'distinct'],
        ]);

        $service->start($validated['team_order']);

        return redirect()->route('manager.draft')->with('status', 'Country draft started.');
    }

    public function cancel(AuctionDraft $draft): RedirectResponse
    {
        if ($draft->status !== 'active') {
            return back()->withErrors(['draft' => 'This draft is not active.']);
        }

        $draft->update(['status' => 'completed', 'ended_at' => now()]);

        return redirect()->route('admin.auctions.index')->with('status', 'Draft cancelled.');
    }
}
