@extends('layouts.app')

@section('title', 'League Settings')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white">League Settings</h1>
        <p class="mt-2 text-slate-400">Configure transfer windows, valuation rules, and standout thresholds</p>
    </div>

    <div class="cricket-card rounded-2xl p-8 max-w-2xl">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="space-y-4 border-b border-slate-700 pb-6">
                <h3 class="text-lg font-bold text-white">Transfer Window</h3>

                <div>
                    <label class="block text-sm font-bold text-white mb-2">Transfer Window Duration (days)</label>
                    <input type="number" name="transfer_window_days" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ $settings['transfer_window_days']['value'] ?? 15 }}" min="1" max="90" required>
                    <p class="text-xs text-slate-500 mt-1">How many days the transfer market is open</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-white mb-2">Transfer Cycle (days)</label>
                    <input type="number" name="transfer_cycle_days" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ $settings['transfer_cycle_days']['value'] ?? 60 }}" min="1" max="365" required>
                    <p class="text-xs text-slate-500 mt-1">How often the transfer window opens (in days)</p>
                </div>
            </div>

            <div class="space-y-4 border-b border-slate-700 pb-6">
                <h3 class="text-lg font-bold text-white">Valuation Rules</h3>

                <div>
                    <label class="block text-sm font-bold text-white mb-2">Win Bonus (%)</label>
                    <input type="number" name="win_bonus_percentage" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ $settings['win_bonus_percentage']['value'] ?? 5 }}" min="0" max="100" step="0.5" required>
                    <p class="text-xs text-slate-500 mt-1">% value increase for winning team players</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-white mb-2">Loss Penalty (%)</label>
                    <input type="number" name="loss_penalty_percentage" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ $settings['loss_penalty_percentage']['value'] ?? 3 }}" min="0" max="100" step="0.5" required>
                    <p class="text-xs text-slate-500 mt-1">% value decrease for losing team players (unless standout)</p>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-bold text-white">Standout Thresholds</h3>
                <p class="text-xs text-slate-400">If a losing player hits these, they get a BONUS instead of a penalty</p>

                <div>
                    <label class="block text-sm font-bold text-white mb-2">Standout Runs (minimum)</label>
                    <input type="number" name="standout_threshold_runs" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ $settings['standout_threshold_runs']['value'] ?? 50 }}" min="0" required>
                    <p class="text-xs text-slate-500 mt-1">Minimum runs to qualify as standout performance</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-white mb-2">Standout Wickets (minimum)</label>
                    <input type="number" name="standout_threshold_wickets" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ $settings['standout_threshold_wickets']['value'] ?? 3 }}" min="0" required>
                    <p class="text-xs text-slate-500 mt-1">Minimum wickets to qualify as standout performance</p>
                </div>
            </div>

            <div class="flex gap-3 pt-6">
                <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold transition">
                    Save Settings
                </button>
                <a href="{{ route('admin.panel') }}" class="flex-1 px-6 py-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
