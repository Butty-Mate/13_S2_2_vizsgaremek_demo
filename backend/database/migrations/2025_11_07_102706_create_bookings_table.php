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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('camping_id')->constrained()->onDelete('cascade');
            $table->foreignId('camping_spot_id')->constrained()->onDelete('cascade');
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->string('status')->default('pending'); // pending, confirmed, cancelled
            $table->timestamps();
            
            // Indexek a gyorsabb kereséshez
            $table->index(['camping_spot_id', 'arrival_date', 'departure_date']);
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
