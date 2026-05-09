<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('avatar');
            $table->enum('gender', ['man', 'woman', 'other'])->nullable()->after('bio');
            $table->date('dob')->nullable()->after('gender');
            $table->string('location')->nullable()->after('dob');
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->enum('intent', ['friends', 'relationship', 'casual'])->nullable()->after('longitude');
            $table->boolean('is_premium')->default(false)->after('intent');
            $table->boolean('profile_complete')->default(false)->after('is_premium');
            $table->json('filter_preferences')->nullable()->after('profile_complete');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone','avatar','bio','gender','dob',
                'location','latitude','longitude','intent',
                'is_premium','profile_complete','filter_preferences',
            ]);
        });
    }
};
