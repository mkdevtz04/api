<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'email' => 'nullable|email|exists:users,email',
        ]);

        $viewer = null;

        if (! empty($validated['email'])) {
            $viewer = User::where('email', $validated['email'])->first();
        }

        $profiles = User::query()
            ->with('interests')
            ->when(
                $viewer,
                fn ($query) => $query->where('id', '!=', $viewer->id)
            )
            ->whereNotNull('avatar')
            ->where('profile_complete', true)
            ->latest()
            ->take(12)
            ->get()
            ->values()
            ->map(function (User $user, int $index) use ($viewer) {
                $distanceKm = $viewer && $viewer->latitude && $viewer->longitude
                    ? round($user->distanceFrom((float) $viewer->latitude, (float) $viewer->longitude))
                    : $index + 1;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'age' => $user->age,
                    'bio' => $user->bio,
                    'avatar' => $user->avatar,
                    'location' => $user->location ?: 'Chicago, IL',
                    'distance_km' => max(1, (int) $distanceKm),
                    'interests' => $user->interests->pluck('interest')->values(),
                ];
            });

        return response()->json([
            'location' => $viewer?->location ?: 'Chicago, IL',
            'profiles' => $profiles,
        ]);
    }
}
