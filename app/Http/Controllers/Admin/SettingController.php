<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Services\AuctionDraftService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function index(AuctionDraftService $draftService)
    {
        $settings = Setting::all()->keyBy('key')->toArray();
        $transferWindowOpen = Setting::isTransferWindowOpen();
        $manuallyClosed = ! Setting::getValue('transfer_window.open', true);
        $auctionStatus = Setting::auctionStatus();
        $cycleSummary = Setting::transferWindowCycleSummary();
        $draftTurnTimeoutSeconds = $draftService->turnTimeoutSeconds();

        return view('admin.settings.index', compact(
            'settings', 'transferWindowOpen', 'manuallyClosed', 'auctionStatus', 'cycleSummary', 'draftTurnTimeoutSeconds'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'transfer_window_open_days' => 'required|integer|min:1|max:90',
            'transfer_window_closed_days' => 'required|integer|min:0|max:365',
            'draft_turn_timeout_seconds' => 'required|integer|min:10|max:600',
            'win_bonus_percentage' => 'required|numeric|min:0|max:100',
            'loss_penalty_percentage' => 'required|numeric|min:0|max:100',
            'standout_threshold_runs' => 'required|integer|min:0',
            'standout_threshold_wickets' => 'required|integer|min:0',
        ]);

        Setting::setValue('transfer_window.open_days', $validated['transfer_window_open_days'], 'integer');
        Setting::setValue('transfer_window.closed_days', $validated['transfer_window_closed_days'], 'integer');
        Setting::setValue('draft.turn_timeout_seconds', $validated['draft_turn_timeout_seconds'], 'integer');

        foreach (['win_bonus_percentage', 'loss_penalty_percentage', 'standout_threshold_runs', 'standout_threshold_wickets'] as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $validated[$key], 'updated_by_user_id' => auth()->id()]
            );
        }

        return redirect()->back()->with('status', 'League settings updated successfully.');
    }

    public function toggleTransferWindow(Request $request)
    {
        $current = Setting::getValue('transfer_window.open', true);

        Setting::updateOrCreate(
            ['key' => 'transfer_window.open'],
            ['value' => $current ? '0' : '1', 'type' => 'boolean', 'updated_by_user_id' => auth()->id()]
        );

        $status = $current ? 'force-closed' : 'released back to the schedule';

        return redirect()->back()->with('status', "Transfer window {$status}.");
    }

    public function updateAuctionStatus(Request $request)
    {
        $validated = $request->validate([
            'auction_status' => 'required|in:open,announced,closed',
        ]);

        Setting::setAuctionStatus($validated['auction_status'], auth()->id());

        return redirect()->back()->with('status', "Auction status set to " . ucfirst($validated['auction_status']) . '.');
    }
}
