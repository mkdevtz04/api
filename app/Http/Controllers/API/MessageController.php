<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\UserMatch;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * List messages for a match conversation.
     */
    public function index(Request $request, int $matchId)
    {
        $userId = $request->user()->id;
        $match  = $this->findMatchOrFail($matchId, $userId);

        $messages = Message::where('match_id', $matchId)
            ->with('sender:id,name,avatar')
            ->latest()
            ->paginate(40);

        // Mark unread messages as read
        Message::where('match_id', $matchId)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    /**
     * Send a message within a match.
     */
    public function store(Request $request, int $matchId)
    {
        $userId = $request->user()->id;
        $match  = $this->findMatchOrFail($matchId, $userId);

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
            'type' => 'nullable|in:text,image,sticker,voice',
        ]);

        $message = Message::create([
            'match_id'  => $matchId,
            'sender_id' => $userId,
            'body'      => $validated['body'],
            'type'      => $validated['type'] ?? 'text',
        ]);

        return response()->json($message->load('sender:id,name,avatar'), 201);
    }

    /**
     * Mark a specific message as read.
     */
    public function markRead(Request $request, int $id)
    {
        $message = Message::findOrFail($id);

        if ($message->sender_id !== $request->user()->id) {
            $message->update(['read_at' => now()]);
        }

        return response()->json($message);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function findMatchOrFail(int $matchId, int $userId): UserMatch
    {
        return UserMatch::where('id', $matchId)
            ->where(function ($q) use ($userId) {
                $q->where('user1_id', $userId)->orWhere('user2_id', $userId);
            })
            ->firstOrFail();
    }
}
