<?php

use App\Enums\UnitCondition;
use App\Enums\UnitStatus;
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
        Schema::create('item_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();

            $table->string('label', 20);
            $table->enum('condition', array_column(UnitCondition::cases(), 'value'));
            $table->enum('status', array_column(UnitStatus::cases(), 'value'))->default(UnitStatus::Active->value);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_units');
    }
};
