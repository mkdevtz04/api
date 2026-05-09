<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserMatch;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    /**
     * Return all matches for the authenticated user with last message.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $matches = UserMatch::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->with([
                'user1:id,name,avatar,latitude,longitude',
                'user2:id,name,avatar,latitude,longitude',
                'messages' => fn($q) => $q->latest()->limit(1),
            ])
            ->latest()
            ->paginate(20);

        // Transform: attach the "other" user and unread count to each match
        $matches->getCollection()->transform(function ($match) use ($userId) {
            $other = $match->user1_id === $userId ? $match->user2 : $match->user1;
            $unread = $match->messages()
                ->where('sender_id', '!=', $userId)
                ->whereNull('read_at')
                ->count();

            return [
                'id'           => $match->id,
                'other_user'   => $other,
                'last_message' => $match->messages->first(),
                'unread_count' => $unread,
                'created_at'   => $match->created_at,
            ];
        });

        return response()->json($matches);
    }
}
