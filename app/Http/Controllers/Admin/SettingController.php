<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'transfer_window_days' => 'required|integer|min:1|max:90',
            'transfer_cycle_days' => 'required|integer|min:1|max:365',
            'win_bonus_percentage' => 'required|numeric|min:0|max:100',
            'loss_penalty_percentage' => 'required|numeric|min:0|max:100',
            'standout_threshold_runs' => 'required|integer|min:0',
            'standout_threshold_wickets' => 'required|integer|min:0',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'updated_by_user_id' => auth()->id()]
            );
        }

        return redirect()->back()->with('status', 'League settings updated successfully.');
    }
}
