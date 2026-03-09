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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year_from');
            $table->smallInteger('year_to')->nullable();
            $table->enum('date_precision', ['exact', 'year', 'decade', 'circa'])->default('circa');
            $table->text('description')->nullable();
            $table->decimal('heading', 6, 2)->nullable();
            $table->decimal('pitch', 6, 2)->nullable();
            $table->foreignId('place_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source_credit')->nullable();
            $table->string('phash', 64)->nullable()->index();
            $table->timestamps();

            $table->index(['year_from', 'year_to']);
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
