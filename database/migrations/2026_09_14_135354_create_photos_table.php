<?php

use App\Enums\PhotoStatus;
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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('caption')->nullable();
            $table->text('context_story');
            $table->string('image_path');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->date('taken_at')->nullable();
            $table->string('consent_type');
            $table->string('status')->default(PhotoStatus::Pending->value);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
