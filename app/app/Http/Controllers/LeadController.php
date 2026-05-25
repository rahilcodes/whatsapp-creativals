<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Message;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Display a listing of the leads.
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $query = Lead::query();

        // Apply filters
        switch ($filter) {
            case 'high_score':
                $query->where('lead_score', '>=', 70);
                break;
            case 'missing_contact':
                $query->where(function ($q) {
                    $q->whereNull('captured_name')
                      ->orWhereNull('captured_email')
                      ->orWhere(function ($sq) {
                          $sq->whereNull('captured_phone');
                      });
                });
                break;
            case 'human_required':
                $query->where('human_required', true);
                break;
            case 'recent':
                $query->orderByDesc('last_activity_at');
                break;
            default:
                $query->orderByDesc('lead_score');
                break;
        }

        if ($filter !== 'recent') {
            $query->orderByDesc('last_activity_at');
        }

        // JSON mode for auto-polling from the leads dashboard
        if ($request->query('json') === '1') {
            $leads = $query->limit(50)->get();
            return response()->json([
                'leads' => $leads,
                'total' => $leads->count(),
                'synced_at' => now()->toISOString(),
            ]);
        }

        $leads = $query->paginate(20)->withQueryString();

        return view('leads.index', compact('leads', 'filter'));
    }

    /**
     * Get details for a specific lead via JSON for the slide-over panel.
     */
    public function show($id)
    {
        $lead = Lead::findOrFail($id);

        // Fetch last 30 messages for timeline
        $messages = Message::where('phone', $lead->phone)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'lead' => $lead,
            'messages' => $messages,
        ]);
    }
}
