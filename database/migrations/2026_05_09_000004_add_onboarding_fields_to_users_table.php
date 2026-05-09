<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('custom_gender')->nullable()->after('gender');
            $table->boolean('find_friends_enabled')->default(false)->after('profile_complete');
            $table->boolean('notifications_enabled')->default(false)->after('find_friends_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'custom_gender',
                'find_friends_enabled',
                'notifications_enabled',
            ]);
        });
    }
};
