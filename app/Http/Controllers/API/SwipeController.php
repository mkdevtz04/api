<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Swipe;
use App\Models\UserMatch;
use Illuminate\Http\Request;

class SwipeController extends Controller
{
    /**
     * Record a swipe and detect a mutual match.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'swiped_id' => 'required|exists:users,id|different:' . $request->user()->id,
            'direction' => 'required|in:left,right,super',
        ]);

        $swiperId = $request->user()->id;
        $swipedId = $validated['swiped_id'];

        // Record or update the swipe
        $swipe = Swipe::updateOrCreate(
            ['swiper_id' => $swiperId, 'swiped_id' => $swipedId],
            ['direction' => $validated['direction']]
        );

        $isMatch = false;
        $match   = null;

        // Check for mutual like
        if (in_array($validated['direction'], ['right', 'super'])) {
            $mutual = Swipe::where('swiper_id', $swipedId)
                ->where('swiped_id', $swiperId)
                ->whereIn('direction', ['right', 'super'])
                ->exists();

            if ($mutual) {
                $match = UserMatch::firstOrCreate([
                    'user1_id' => min($swiperId, $swipedId),
                    'user2_id' => max($swiperId, $swipedId),
                ]);
                $isMatch = true;
            }
        }

        return response()->json([
            'swipe'    => $swipe,
            'is_match' => $isMatch,
            'match'    => $match,
        ]);
    }
}
