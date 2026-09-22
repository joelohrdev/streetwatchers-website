<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('timezone')->default('UTC')->after('ends_at');
            $table->boolean('rsvps_enabled')->default(false)->after('timezone');
            $table->unsignedInteger('rsvp_limit')->nullable()->after('rsvps_enabled');
            $table->timestamp('cancelled_at')->nullable()->after('rsvp_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'rsvps_enabled', 'rsvp_limit', 'cancelled_at']);
        });
    }
};
