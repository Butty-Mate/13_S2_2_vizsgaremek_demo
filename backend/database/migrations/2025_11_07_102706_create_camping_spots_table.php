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
        Schema::create('camping_spots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camping_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('type')->nullable(); // sátor, lakókocsi, stb.
            $table->integer('capacity')->default(1);
            $table->integer('price_per_night');
            $table->boolean('is_available')->default(true);
            $table->text('description')->nullable();
            $table->integer('row');
            $table->integer('column');
            $table->float('rating')->nullable();
            $table->string('tags')->nullable(); // pl. "árnyékos,tópart"
            $table->string('services')->nullable(); // pl. "wifi,áram,víz"
            $table->timestamps();
            
            // Egyedi rácspozíció per kemping
            $table->unique(['camping_id', 'row', 'column']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camping_spots');
    }
};
