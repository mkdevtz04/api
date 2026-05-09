<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        // logger($request->all());
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('dating-app')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login with email and password.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        /** @var User $user */
        $user  = Auth::user();
        $token = $user->createToken('dating-app')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * Return the authenticated user.
     */
    public function me(Request $request)
    {
        return response()->json($request->user()->load('interests', 'photos'));
    }

    /**
     * Logout (revoke current token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Send OTP to phone number.
     */
    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required|string|min:9|max:15']);

        $code = rand(100000, 999999);

        // Store in cache for 5 minutes (keyed by phone)
        cache()->put("otp:{$request->phone}", $code, now()->addMinutes(5));

        // TODO: Send via Africa's Talking / Twilio SMS
        // For dev — return code in response
        return response()->json([
            'message' => 'OTP sent successfully.',
            'dev_code' => config('app.debug') ? $code : null,
        ]);
    }

    /**
     * Verify OTP and return token.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code'  => 'required|string|size:6',
        ]);

        $cached = cache()->get("otp:{$request->phone}");

        if (!$cached || (string) $cached !== (string) $request->code) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        cache()->forget("otp:{$request->phone}");

        // Find or create user by phone
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            ['name' => 'User', 'password' => Hash::make(str()->random(16))]
        );

        $token = $user->createToken('dating-app')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    /**
     * Send OTP to email address.
     */
    public function sendEmailOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $code = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // Store in cache for 5 minutes (keyed by email)
        cache()->put("email_otp:{$request->email}", $code, now()->addMinutes(5));

        // Send email with OTP via Mailtrap
        try {
            Mail::send('emails.otp', ['code' => $code], function($message) use ($request) {
                $message->to($request->email)
                       ->subject('Your Dating App Verification Code');
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send email. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'OTP sent successfully to email.',
        ]);
    }

    /**
     * Verify email OTP.
     */
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:4',
        ]);

        $cached = cache()->get("email_otp:{$request->email}");

        if (!$cached || (string) $cached !== (string) $request->code) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        cache()->forget("email_otp:{$request->email}");

        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => strtok($request->email, '@') ?: 'User',
                'password' => Hash::make(str()->random(32)),
            ]
        );

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        $token = null;
        $nextStep = 'complete_profile';

        if ($user->profile_complete) {
            $token = $user->createToken('dating-app')->plainTextToken;
            $nextStep = 'home';
            cache()->forget("email_verified:{$request->email}");
        } else {
            // Mark email as verified for the profile completion step
            cache()->put("email_verified:{$request->email}", true, now()->addHours(1));
        }

        return response()->json([
            'message' => 'Email verified successfully.',
            'verified' => true,
            'next_step' => $nextStep,
            'user' => $user->fresh(),
            'token' => $token,
        ]);
    }

    /**
     * Complete the email signup flow after OTP verification.
     */
    public function completeEmailSignup(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date|before_or_equal:' . now()->subYears(18)->toDateString(),
        ]);

        $isVerifiedInFlow = cache()->get("email_verified:{$validated['email']}");
        $user = User::where('email', $validated['email'])->firstOrFail();

        if (! $isVerifiedInFlow && ! $user->email_verified_at) {
            return response()->json([
                'message' => 'Verify your email with OTP before completing profile.',
            ], 422);
        }

        $user->update([
            'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'dob' => $validated['dob'],
            'profile_complete' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        cache()->forget("email_verified:{$validated['email']}");

        $token = $user->createToken('dating-app')->plainTextToken;

        return response()->json([
            'message' => 'Profile completed successfully.',
            'user' => $user->fresh(),
            'token' => $token,
        ]);
    }
}
