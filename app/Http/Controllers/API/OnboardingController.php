<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInterest;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function updateGender(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'gender' => 'required|in:man,woman,other',
            'custom_gender' => 'nullable|string|max:255',
        ]);

        $user = $this->verifiedUser($validated['email']);

        $user->update([
            'gender' => $validated['gender'],
            'custom_gender' => $validated['gender'] === 'other'
                ? ($validated['custom_gender'] ?? null)
                : null,
        ]);

        return response()->json([
            'message' => 'Gender saved successfully.',
            'user' => $user->fresh(),
        ]);
    }

    public function updateInterests(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'interests' => 'required|array|min:1|max:8',
            'interests.*' => 'required|string|max:50',
        ]);

        $user = $this->verifiedUser($validated['email']);

        UserInterest::where('user_id', $user->id)->delete();

        $rows = collect($validated['interests'])
            ->map(fn ($interest) => trim($interest))
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($interest) => [
                'user_id' => $user->id,
                'interest' => $interest,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if (! empty($rows)) {
            UserInterest::insert($rows);
        }

        return response()->json([
            'message' => 'Interests saved successfully.',
            'interests' => collect($rows)->pluck('interest')->values(),
        ]);
    }

    public function updateFriendsPermission(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'find_friends_enabled' => 'required|boolean',
        ]);

        $user = $this->verifiedUser($validated['email']);

        $user->update([
            'find_friends_enabled' => $validated['find_friends_enabled'],
        ]);

        return response()->json([
            'message' => 'Friends preference saved successfully.',
            'user' => $user->fresh(),
        ]);
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'notifications_enabled' => 'required|boolean',
        ]);

        $user = $this->verifiedUser($validated['email']);

        $user->update([
            'profile_complete' => true,
            'notifications_enabled' => $validated['notifications_enabled'],
        ]);

        return response()->json([
            'message' => 'Notification preference saved successfully.',
            'user' => $user->fresh(),
        ]);
    }

    private function verifiedUser(string $email): User
    {
        $user = User::where('email', $email)->firstOrFail();

        if (! $user->email_verified_at) {
            abort(response()->json([
                'message' => 'Verify your email first before continuing onboarding.',
            ], 422));
        }

        return $user;
    }
}
