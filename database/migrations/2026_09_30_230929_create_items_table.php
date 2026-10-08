<?php

use App\Enums\ItemStatus;
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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_profile_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            $table->string('name');
            $table->string('series')->nullable();
            $table->string('character')->nullable();
            $table->string('size')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('daily_rate');
            $table->unsignedInteger('deposit');
            $table->enum('status', array_column(ItemStatus::cases(), 'value'))->default(ItemStatus::Draft->value);

            $table->timestamp('taken_down_at')->nullable();
            $table->foreignId('taken_down_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('takedown_reason')->nullable();

            $table->timestamps();

            $table->index(['status', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
