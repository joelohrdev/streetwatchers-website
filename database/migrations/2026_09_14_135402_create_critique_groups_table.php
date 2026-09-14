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
        Schema::create('critique_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->date('week_start');
            $table->date('week_end');
            $table->unsignedInteger('max_members')->default(8);
            $table->string('status')->default('forming');
            $table->timestamps();

            $table->index(['status', 'week_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('critique_groups');
    }
};
