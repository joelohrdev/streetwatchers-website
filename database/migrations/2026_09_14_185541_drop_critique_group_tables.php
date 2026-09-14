<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * StreetWatchers no longer offers feedback or critique on photography, so the critique group
 * tables are removed. Reports about critique comments go with them, since the reported
 * content no longer exists. Audit log entries are kept for accountability.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('reports')->where('reportable_type', 'critique_comment')->delete();

        Schema::dropIfExists('critique_comments');
        Schema::dropIfExists('critique_submissions');
        Schema::dropIfExists('critique_group_user');
        Schema::dropIfExists('critique_groups');
    }

    /**
     * Reverse the migrations. This recreates empty tables; dropped rows cannot be restored.
     */
    public function down(): void
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

        Schema::create('critique_group_user', function (Blueprint $table) {
            $table->foreignId('critique_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->primary(['critique_group_id', 'user_id']);
        });

        Schema::create('critique_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('critique_group_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('photo_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('critique_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('critique_submission_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }
};
