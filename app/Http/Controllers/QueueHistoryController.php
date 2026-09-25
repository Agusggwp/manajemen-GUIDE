<?php

namespace App\Http\Controllers;

use App\Models\QueueHistory;
use Illuminate\Http\Request;

class QueueHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = QueueHistory::with(['selectedGuide', 'booking', 'assigner'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('guide_id')) {
            $query->where('selected_guide_id', $request->guide_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $histories = $query->paginate(20)->withQueryString();

        return view('queue.history', compact('histories'));
    }
}
