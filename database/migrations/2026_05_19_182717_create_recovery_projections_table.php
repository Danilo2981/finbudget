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
        Schema::create('recovery_projections', function (Blueprint $table) {
            $table->id();
            $table->integer('row_index');
            $table->string('concept');
            $table->integer('mes');
            $table->decimal('valor', 15, 2)->default(0.00);
            $table->timestamps();

            // Unique constraint to prevent duplicate month entries for same row segment
            $table->unique(['row_index', 'mes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_projections');
    }
};
