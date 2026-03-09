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
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('point_action_id')->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->string('actionable_type');
            $table->unsignedBigInteger('actionable_id');
            $table->timestamps();

            $table->unique(['user_id', 'point_action_id', 'actionable_type', 'actionable_id'], 'point_transactions_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
