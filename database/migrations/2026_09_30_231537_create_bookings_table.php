<?php

use App\Enums\BookingStatus;
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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->ulid('reference')->unique();
            $table->foreignId('item_unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('renter_id')->constrained('users')->restrictOnDelete();

            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total');
            $table->unsignedInteger('deposit');
            $table->enum('status', array_column(BookingStatus::cases(), 'value'))->default(BookingStatus::Pending->value);
            $table->timestamp('returned_at')->nullable();

            $table->timestamps();

            $table->index(['item_unit_id', 'status', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
