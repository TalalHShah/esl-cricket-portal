@extends('layouts.admin')

@section('title', 'League Settings')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white">League Settings</h1>
        <p class="mt-2 text-slate-400">Configure transfer windows, valuation rules, and standout thresholds</p>
    </div>

    <div class="cricket-card rounded-2xl p-8 max-w-2xl mb-6">
        <div class="flex items-center justify-between gap-4 flex-wrap mb-4">
            <div>
                <h3 class="text-lg font-bold text-white">Transfer Window Status</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Currently
                    <span class="font-bold {{ $transferWindowOpen ? 'text-emerald-400' : 'text-red-400' }}">{{ $transferWindowOpen ? 'OPEN' : 'CLOSED' }}</span>
                    — managers {{ $transferWindowOpen ? 'can' : 'cannot' }} make transfers or scout free agents right now.
                </p>
                @if ($auctionStatus !== 'closed')
                    <p class="text-xs text-amber-400 mt-1">Locked closed while the auction is {{ $auctionStatus }} — <a href="{{ route('admin.panel') }}" class="underline">change the Auction Status</a> to Closed to let the schedule below take over.</p>
                @elseif ($manuallyClosed)
                    <p class="text-xs text-amber-400 mt-1">Manually force-closed — the schedule below is paused until you release it.</p>
                @elseif ($cycleSummary)
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $cycleSummary['is_open_portion'] ? 'Closes' : 'Opens' }} in {{ $cycleSummary['days_until_change'] }} day{{ $cycleSummary['days_until_change'] === 1 ? '' : 's' }}
                        (cycle started {{ $cycleSummary['cycle_start']->toFormattedDateString() }})
                    </p>
                @endif
            </div>
            @if ($auctionStatus === 'closed')
                <form action="{{ route('admin.settings.transfer-window.toggle') }}" method="POST">
                    @csrf
                    @if (! $manuallyClosed)
                        <button type="submit" class="px-6 py-3 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold transition">Force Close Now</button>
                    @else
                        <button type="submit" class="px-6 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition">Release Back To Schedule</button>
                    @endif
                </form>
            @endif
        </div>
    </div>

    <div class="cricket-card rounded-2xl p-8 max-w-2xl">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="space-y-4 border-b border-slate-700 pb-6">
                <h3 class="text-lg font-bold text-white">Transfer Window Schedule</h3>
                <p class="text-xs text-slate-400">Once the auction is Closed, the window automatically repeats this open/closed cycle — e.g. 15 days open, 15 days closed, on and on — until you open the auction again.</p>

                <div>
                    <label class="block text-sm font-bold text-white mb-2">Open For (days)</label>
                    <input type="number" name="transfer_window_open_days" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ $settings['transfer_window.open_days']['value'] ?? 15 }}" min="1" max="90" required>
                    <p class="text-xs text-slate-500 mt-1">How many days the window stays open each cycle</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-white mb-2">Then Closed For (days)</label>
                    <input type="number" name="transfer_window_closed_days" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ $settings['transfer_window.closed_days']['value'] ?? 15 }}" min="0" max="365" required>
                    <p class="text-xs text-slate-500 mt-1">How many days it then stays closed, before reopening automatically (e.g. 60 for two months)</p>
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
