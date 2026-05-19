<?php

namespace App\Services;

use App\Models\Projection;

class BudgetEngine
{
    /**
     * Calcula la proyección mes a mes durante 12 meses.
     */
    public function calculate(Projection $projection)
    {
        $params = $projection->parameters;
        
        // Saldos históricos hardcodeados temporalmente (diciembre 2025)
        $saldosIniciales = [
            'productive'   => 5000000,
            'consumer'     => 3000000,
            'microcredit'  => 1000000,
            'refinanced'   => 500000,
            'restructured' => 200000,
            'sight'        => 3463886,
            'term'         => 59300337,
        ];

        $required_placement = [
            'productive'   => ($saldosIniciales['productive'] * $params->target_growth_productive) + $params->recovery_productive,
            'consumer'     => ($saldosIniciales['consumer'] * $params->target_growth_consumer) + $params->recovery_consumer,
            'microcredit'  => ($saldosIniciales['microcredit'] * $params->target_growth_microcredit) + $params->recovery_microcredit,
            'refinanced'   => ($saldosIniciales['refinanced'] * $params->target_growth_refinanced) + $params->recovery_refinanced,
            'restructured' => ($saldosIniciales['restructured'] * $params->target_growth_restructured) + $params->recovery_restructured,
        ];

        $resultados = [
            'colocacion_requerida' => $required_placement,
            'months' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            'cartera' => ['productive' => [], 'consumer' => [], 'microcredit' => [], 'refinanced' => [], 'restructured' => [], 'total' => []],
            'int_cartera' => ['productive' => [], 'consumer' => [], 'microcredit' => [], 'total' => []],
            'captaciones' => ['sight' => [], 'term' => [], 'total' => []],
            'int_captaciones' => ['sight' => [], 'term' => [], 'total' => []],
            'estado_resultados' => [
                'ingresos_financieros' => [],
                'otros_ingresos' => [],
                'gastos_financieros' => [],
                'margen_bruto' => [],
                'gastos_operativos' => [],
                'utilidad_neta' => []
            ]
        ];

        $actual = $saldosIniciales;

        for ($i = 0; $i < 12; $i++) {
            // Crecimiento de saldos de Cartera (Colocación Mensual Requerida - Recuperación Mensual)
            $actual['productive']   += ($required_placement['productive'] / 12) - ($params->recovery_productive / 12);
            $actual['consumer']     += ($required_placement['consumer'] / 12) - ($params->recovery_consumer / 12);
            $actual['microcredit']  += ($required_placement['microcredit'] / 12) - ($params->recovery_microcredit / 12);
            $actual['refinanced']   += ($required_placement['refinanced'] / 12) - ($params->recovery_refinanced / 12);
            $actual['restructured'] += ($required_placement['restructured'] / 12) - ($params->recovery_restructured / 12);
            
            // Captaciones siguen creciendo por tasa
            $actual['sight']        *= (1 + $params->sight_deposit_growth_rate);
            $actual['term']         *= (1 + $params->term_deposit_growth_rate);

            $resultados['cartera']['productive'][] = $actual['productive'];
            $resultados['cartera']['consumer'][] = $actual['consumer'];
            $resultados['cartera']['microcredit'][] = $actual['microcredit'];
            $resultados['cartera']['refinanced'][] = $actual['refinanced'];
            $resultados['cartera']['restructured'][] = $actual['restructured'];
            
            $resultados['cartera']['total'][] = $actual['productive'] + $actual['consumer'] + $actual['microcredit'] + $actual['refinanced'] + $actual['restructured'];

            $resultados['captaciones']['sight'][] = $actual['sight'];
            $resultados['captaciones']['term'][] = $actual['term'];
            $resultados['captaciones']['total'][] = $actual['sight'] + $actual['term'];

            // Cálculo de Intereses (Ingresos)
            $intProd  = $actual['productive'] * ($params->productive_interest_rate / 12);
            $intCons  = $actual['consumer'] * ($params->consumer_interest_rate / 12);
            $intMicro = $actual['microcredit'] * ($params->microcredit_interest_rate / 12);
            $resultados['int_cartera']['productive'][] = $intProd;
            $resultados['int_cartera']['consumer'][] = $intCons;
            $resultados['int_cartera']['microcredit'][] = $intMicro;
            $resultados['int_cartera']['total'][] = $intProd + $intCons + $intMicro;

            // Intereses Pagados (Gastos Financieros)
            $intSight = $actual['sight'] * ($params->sight_deposit_interest_rate / 12);
            $intTerm = $actual['term'] * ($params->term_deposit_interest_rate / 12);
            $resultados['int_captaciones']['sight'][] = $intSight;
            $resultados['int_captaciones']['term'][] = $intTerm;
            $resultados['int_captaciones']['total'][] = $intSight + $intTerm;

            // Estado de Resultados
            $totalIngresos = $intProd + $intCons + $intMicro;
            $otrosIngresos = $params->recovery_written_off + $params->reversal_provisions;
            $totalGastosFin = $intSight + $intTerm;
            
            $margen = ($totalIngresos + $otrosIngresos) - $totalGastosFin;
            
            $gastosFijos = $params->operating_expenses + $params->tech_investment + $params->image_investment;
            $utilidad = $margen - $gastosFijos;

            $resultados['estado_resultados']['ingresos_financieros'][] = $totalIngresos;
            $resultados['estado_resultados']['otros_ingresos'][] = $otrosIngresos;
            $resultados['estado_resultados']['gastos_financieros'][] = $totalGastosFin;
            $resultados['estado_resultados']['margen_bruto'][] = $margen;
            $resultados['estado_resultados']['gastos_operativos'][] = $gastosFijos;
            $resultados['estado_resultados']['utilidad_neta'][] = $utilidad;
        }

        return $resultados;
    }
}
