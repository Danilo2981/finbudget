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
        Schema::create('projection_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projection_id')->constrained()->cascadeOnDelete();
            
            // --- METAS DE CRECIMIENTO ANUAL (%) ---
            $table->decimal('target_growth_productive', 8, 4)->default(0.0000);
            $table->decimal('target_growth_consumer', 8, 4)->default(0.6200);
            $table->decimal('target_growth_microcredit', 8, 4)->default(0.2000);
            $table->decimal('target_growth_refinanced', 8, 4)->default(0.0000);
            $table->decimal('target_growth_restructured', 8, 4)->default(0.0000);

            // --- RECUPERACIONES ADICIONALES ---
            $table->decimal('recovery_refinanced', 15, 2)->default(99983.89);
            $table->decimal('recovery_restructured', 15, 2)->default(138290.52);

            // --- TASAS DE CRECIMIENTO MENSUAL (Captaciones) ---
            $table->decimal('sight_deposit_growth_rate', 8, 4)->default(0.0150);
            $table->decimal('term_deposit_growth_rate', 8, 4)->default(0.0200);

            // --- TASAS DE INTERÉS ANUAL ---
            $table->decimal('productive_interest_rate', 8, 4)->default(0.0986);
            $table->decimal('consumer_interest_rate', 8, 4)->default(0.1500);
            $table->decimal('microcredit_interest_rate', 8, 4)->default(0.2200);
            $table->decimal('sight_deposit_interest_rate', 8, 4)->default(0.0250);
            $table->decimal('term_deposit_interest_rate', 8, 4)->default(0.0800);

            // --- RECUPERACIONES MENSUALES ESPERADAS ($) ---
            $table->decimal('recovery_productive', 15, 2)->default(100000.00);
            $table->decimal('recovery_consumer', 15, 2)->default(50000.00);
            $table->decimal('recovery_microcredit', 15, 2)->default(30000.00);
            $table->decimal('recovery_written_off', 15, 2)->default(12000.00); // Activos Castigados
            $table->decimal('reversal_provisions', 15, 2)->default(24000.00); // Reversión de provisiones

            // --- GASTOS E INVERSIONES MENSUALES FIJAS ($) ---
            $table->decimal('tech_investment', 15, 2)->default(10000.00); // Sistemas
            $table->decimal('image_investment', 15, 2)->default(4166.00); // Nueva imagen
            $table->decimal('operating_expenses', 15, 2)->default(150000.00); // Otros operativos

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projection_parameters');
    }
};
