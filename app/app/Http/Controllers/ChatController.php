<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FlaggedConversation;
use App\Models\Message;
use App\Models\UserMemory;
use App\Services\MemoryService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private MemoryService $memory) {}

    // ── GET /chats — List all conversations ───────────────────
    public function index()
    {
        $conversations = Message::conversations();
        $flagged       = FlaggedConversation::all()->keyBy('phone');

        return view('chats.index', compact('conversations', 'flagged'));
    }

    // ── GET /chats/{phone} — Single chat history ──────────────
    public function show(string $phone)
    {
        $messages  = Message::where('phone', $phone)->orderBy('created_at')->get();
        $memory    = UserMemory::where('phone', $phone)->first();
        $flagged   = FlaggedConversation::where('phone', $phone)->first();

        return view('chats.show', compact('phone', 'messages', 'memory', 'flagged'));
    }

    // ── POST /chats/{phone}/takeover — Toggle human control ───
    public function toggleTakeover(string $phone)
    {
        $row = FlaggedConversation::firstOrCreate(['phone' => $phone], [
            'reason' => 'manual_takeover',
            'status' => 'taken_over',
        ]);

        $row->human_takeover = !$row->human_takeover;
        $row->status         = $row->human_takeover ? 'taken_over' : 'resolved';
        $row->save();

        $label = $row->human_takeover ? 'enabled' : 'disabled';
        ActivityLog::record('human_takeover', "Human takeover {$label} for {$phone}", $phone);

        return back()->with('success', "Human takeover {$label} for {$phone}");
    }

    // ── DELETE /chats/{phone}/memory — Clear user memory ─────
    public function clearMemory(string $phone)
    {
        $this->memory->clearMemory($phone);
        ActivityLog::record('memory_cleared', "Memory cleared for {$phone}", $phone);

        return back()->with('success', "Memory cleared for {$phone}");
    }
}
