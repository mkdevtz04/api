<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserInterest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'name' => 'Jessica Parker',
                'email' => 'jessica@example.com',
                'bio' => 'Professional model',
                'gender' => 'woman',
                'dob' => '2001-04-17',
                'location' => 'Chicago, IL',
                'latitude' => 41.8781,
                'longitude' => -87.6298,
                'avatar' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1200&q=80',
                'interests' => ['Traveling', 'Photography', 'Art'],
            ],
            [
                'name' => 'Maya Brooks',
                'email' => 'maya@example.com',
                'bio' => 'Coffee lover and startup designer',
                'gender' => 'woman',
                'dob' => '1998-09-02',
                'location' => 'Chicago, IL',
                'latitude' => 41.8818,
                'longitude' => -87.6231,
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=1200&q=80',
                'interests' => ['Shopping', 'Music', 'Yoga'],
            ],
            [
                'name' => 'Sophia Lane',
                'email' => 'sophia@example.com',
                'bio' => 'Weekend traveler and foodie',
                'gender' => 'woman',
                'dob' => '1999-06-23',
                'location' => 'Chicago, IL',
                'latitude' => 41.8676,
                'longitude' => -87.6162,
                'avatar' => 'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?auto=format&fit=crop&w=1200&q=80',
                'interests' => ['Cooking', 'Traveling', 'Drink'],
            ],
            [
                'name' => 'Ariana Cole',
                'email' => 'ariana@example.com',
                'bio' => 'Fitness coach and early runner',
                'gender' => 'woman',
                'dob' => '1997-11-08',
                'location' => 'Chicago, IL',
                'latitude' => 41.8925,
                'longitude' => -87.6354,
                'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1200&q=80',
                'interests' => ['Run', 'Swimming', 'Tennis'],
            ],
            [
                'name' => 'Nina Flores',
                'email' => 'nina@example.com',
                'bio' => 'Karaoke nights and gallery hopping',
                'gender' => 'woman',
                'dob' => '2000-01-12',
                'location' => 'Chicago, IL',
                'latitude' => 41.8840,
                'longitude' => -87.6487,
                'avatar' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=1200&q=80',
                'interests' => ['Karaoke', 'Art', 'Music'],
            ],
        ];

        foreach ($profiles as $profile) {
            $user = User::updateOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'bio' => $profile['bio'],
                    'gender' => $profile['gender'],
                    'dob' => $profile['dob'],
                    'location' => $profile['location'],
                    'latitude' => $profile['latitude'],
                    'longitude' => $profile['longitude'],
                    'avatar' => $profile['avatar'],
                    'profile_complete' => true,
                    'find_friends_enabled' => true,
                    'notifications_enabled' => true,
                ]
            );

            UserInterest::where('user_id', $user->id)->delete();

            foreach ($profile['interests'] as $interest) {
                UserInterest::create([
                    'user_id' => $user->id,
                    'interest' => $interest,
                ]);
            }
        }
    }
}
