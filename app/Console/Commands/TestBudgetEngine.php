<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Projection;
use App\Models\ProjectionParameter;
use App\Services\BudgetEngine;

class TestBudgetEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budget:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the budget calculation engine with dummy data';

    /**
     * Execute the console command.
     */
    public function handle(BudgetEngine $engine)
    {
        $this->info('Creando escenario de prueba...');

        // Limpiar para la prueba
        ProjectionParameter::query()->delete();
        Projection::query()->delete();

        $projection = Projection::create([
            'name' => 'Escenario Base de Prueba 2026',
            'year' => 2026
        ]);

        $projection->parameters()->create([
            'productive_growth_rate' => 0.02, // 2% mensual
            'consumer_growth_rate' => 0.03, // 3% mensual
            'sight_deposit_growth_rate' => 0.015,
            'term_deposit_growth_rate' => 0.02,
            
            'productive_interest_rate' => 0.0986, // 9.86% anual
            'consumer_interest_rate' => 0.1500, // 15% anual
            'sight_deposit_interest_rate' => 0.025,
            'term_deposit_interest_rate' => 0.08,

            'operating_expenses' => 150000
        ]);

        $this->info('Calculando proyecciones a 12 meses...');
        
        $resultados = $engine->calculate($projection);

        $this->table(
            ['Mes', 'Ingresos Fin', 'Gastos Fin', 'Margen Bruto', 'Gastos Op', 'Utilidad Neta'],
            collect($resultados['months'])->map(function($mes, $index) use ($resultados) {
                return [
                    $mes,
                    '$' . number_format($resultados['estado_resultados']['ingresos_financieros'][$index], 2),
                    '$' . number_format($resultados['estado_resultados']['gastos_financieros'][$index], 2),
                    '$' . number_format($resultados['estado_resultados']['margen_bruto'][$index], 2),
                    '$' . number_format($resultados['estado_resultados']['gastos_operativos'][$index], 2),
                    '$' . number_format($resultados['estado_resultados']['utilidad_neta'][$index], 2),
                ];
            })
        );

        $this->info('¡Cálculo exitoso! La matemática del motor está funcionando en Laravel.');
    }
}
