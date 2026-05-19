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
        Schema::create('master_budget_records', function (Blueprint $table) {
            $table->id();
            $table->integer('nivel')->nullable();
            $table->string('tipo')->nullable();
            $table->string('codigo')->nullable();
            $table->string('cuenta')->nullable();
            $table->integer('mes')->nullable();
            $table->integer('año')->nullable();
            $table->decimal('saldo', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_budget_records');
    }
};
