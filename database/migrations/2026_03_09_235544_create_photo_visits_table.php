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
        Schema::create('photo_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45);
            $table->string('user_agent', 512)->nullable();
            $table->string('referer', 1024)->nullable();
            $table->string('timezone', 100)->nullable();
            $table->string('country', 2)->nullable();
            $table->boolean('is_bot')->default(false);
            $table->timestamp('visited_at');

            $table->index(['photo_id', 'visited_at']);
            $table->index('user_id');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->unsignedInteger('visits_count')->default(0)->after('downvotes_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('visits_count');
        });

        Schema::dropIfExists('photo_visits');
    }
};
