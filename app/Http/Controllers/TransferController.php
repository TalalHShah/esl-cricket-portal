<?php

namespace App\Http\Controllers;

use App\Concerns\Sortable;
use App\Models\Transfer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    use Sortable;

    /**
     * Display a listing of player transfers.
     */
    public function index(Request $request): View
    {
        $query = Transfer::with(['player', 'fromTeam', 'toTeam', 'requestedBy', 'approvedBy'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));

        $sort = $this->applySort($query, $request, [
            'date_desc' => fn ($q) => $q->orderByDesc('created_at'),
            'date_asc' => fn ($q) => $q->orderBy('created_at'),
            'fee_desc' => fn ($q) => $q->orderByDesc('fee'),
            'fee_asc' => fn ($q) => $q->orderBy('fee'),
            'status' => fn ($q) => $q->orderBy('status'),
        ], 'date_desc');

        $transfers = $query->paginate(20)->withQueryString();

        $totals = [
            'count' => Transfer::count(),
            'approved' => Transfer::where('status', 'approved')->count(),
            'pending' => Transfer::where('status', 'pending')->count(),
            'spend' => Transfer::where('status', 'approved')->sum('fee'),
        ];

        return view('transfers.index', compact('transfers', 'totals', 'sort'));
    }
}
