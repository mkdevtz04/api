<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name', 'email', 'phone', 'password',
    'avatar', 'bio', 'gender', 'dob',
    'location', 'latitude', 'longitude',
    'intent', 'is_premium', 'profile_complete',
    'filter_preferences',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at'   => 'datetime',
        'password'            => 'hashed',
        'is_premium'          => 'boolean',
        'profile_complete'    => 'boolean',
        'filter_preferences'  => 'array',
        'dob'                 => 'date',
        'latitude'            => 'decimal:7',
        'longitude'           => 'decimal:7',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    // public function interests(): HasMany
    // {
    //     return $this->hasMany(UserInterest::class);
    // }

    // public function photos(): HasMany
    // {
    //     return $this->hasMany(UserPhoto::class)->orderBy('order');
    // }

    public function swipesGiven(): HasMany
    {
        return $this->hasMany(Swipe::class, 'swiper_id');
    }

    // public function matchesAsUser1(): HasMany
    // {
    //     return $this->hasMany(Match::class, 'user1_id');
    // }

    // public function matchesAsUser2(): HasMany
    // {
    //     return $this->hasMany(Match::class, 'user2_id');
    // }

    // ─── Computed ─────────────────────────────────────────────────────

    public function getAgeAttribute(): ?int
    {
        return $this->dob ? $this->dob->age : null;
    }

    /**
     * Calculate distance from given lat/lng in km (Haversine).
     */
    public function distanceFrom(float $lat, float $lng): float
    {
        if (!$this->latitude || !$this->longitude) return 0;

        $earthRadius = 6371;
        $dLat = deg2rad($lat - $this->latitude);
        $dLng = deg2rad($lng - $this->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($this->latitude)) * cos(deg2rad($lat))
            * sin($dLng / 2) * sin($dLng / 2);

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
