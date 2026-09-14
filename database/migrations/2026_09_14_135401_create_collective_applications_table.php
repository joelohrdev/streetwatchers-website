<?php

use App\Enums\CollectiveApplicationStatus;
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
        Schema::create('collective_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collective_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->string('status')->default(CollectiveApplicationStatus::Pending->value);
            $table->timestamps();

            $table->index(['collective_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collective_applications');
    }
};
