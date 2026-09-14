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
        Schema::table('collectives', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('is_open_for_applications');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collectives', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('is_verified');
        });
    }
};
