<?php

namespace App\Services;

use App\Models\FinancialHistory;
use App\Models\MasterBudgetRecord;
use Illuminate\Support\Facades\DB;

class ProvisionEngine
{
    /**
     * Obtiene los saldos históricos de la última fecha disponible y calcula los ratios por defecto.
     */
    public function calculateHistoricalData()
    {
        // 1. Obtener la última fecha histórica
        $latest = FinancialHistory::select('año', 'mes')
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->first();

        if (!$latest) {
            return [
                'año' => null,
                'mes' => null,
                'portfolio' => [],
                'provisions' => [],
                'ratios' => [
                    'productive' => 0.0073,
                    'consumer' => 0.0172,
                    'microcredit' => 0.0142,
                    'refinanced' => 0.0571,
                    'restructured' => 0.1692,
                ]
            ];
        }

        $year = $latest->año;
        $month = $latest->mes;

        // 2. Obtener saldos de cartera en esa fecha
        $portfolioBalances = [
            'productive' => $this->sumCodes($year, $month, ['1401', '1449', '1425']),
            'consumer' => $this->sumCodes($year, $month, ['1402', '1426', '1450']),
            'microcredit' => $this->sumCodes($year, $month, ['1404', '1428', '1452']),
            'refinanced' => $this->sumCodes($year, $month, ['1410', '1412', '1434', '1436', '1458', '1460']),
            'restructured' => $this->sumCodes($year, $month, ['1418', '1420', '1442', '1444', '1466', '1468']),
        ];

        // 3. Obtener saldos de provisiones en esa fecha (solo códigos hoja para evitar doble conteo con cuentas padre)
        $provisionBalances = [
            'productive' => abs($this->sumCodes($year, $month, ['14990505'])),
            'consumer' => abs($this->sumCodes($year, $month, ['14991005'])),
            'microcredit' => abs($this->sumCodes($year, $month, ['14992005'])),
            'refinanced' => abs($this->sumCodes($year, $month, ['149945'])),
            'restructured' => abs($this->sumCodes($year, $month, ['149950'])),
            'anticiclica' => abs($this->sumCodes($year, $month, ['149985'])),
            'noreversada' => abs($this->sumCodes($year, $month, ['149987'])),
            'voluntaria' => abs($this->sumCodes($year, $month, ['149989'])),
        ];

        // 4. Calcular ratios promedio ponderado utilizando todos los periodos históricos disponibles (todos los años)
        $segmentCodes = [
            'productive' => ['1401', '1449', '1425'],
            'consumer' => ['1402', '1426', '1450'],
            'microcredit' => ['1404', '1428', '1452'],
            'refinanced' => ['1410', '1412', '1434', '1436', '1458', '1460'],
            'restructured' => ['1418', '1420', '1442', '1444', '1466', '1468'],
        ];

        $provisionCodes = [
            'productive' => ['14990505'],
            'consumer' => ['14991005'],
            'microcredit' => ['14992005'],
            'refinanced' => ['149945'],
            'restructured' => ['149950'],
        ];

        $ratios = [];
        foreach ($segmentCodes as $key => $codes) {
            $totalPortfolio = (float) FinancialHistory::whereIn('codigo', $codes)->sum('saldo');
            $provCodes = $provisionCodes[$key];
            $totalProvision = abs((float) FinancialHistory::whereIn('codigo', $provCodes)->sum('saldo'));
            
            $ratios[$key] = $totalPortfolio > 0 ? ($totalProvision / $totalPortfolio) : 0;
        }

        // Fallbacks si las cuentas no tienen saldo
        if ($ratios['productive'] == 0) $ratios['productive'] = 0.0552;
        if ($ratios['consumer'] == 0) $ratios['consumer'] = 0.0483;
        if ($ratios['microcredit'] == 0) $ratios['microcredit'] = 0.0489;
        if ($ratios['refinanced'] == 0) $ratios['refinanced'] = 0.1835;
        if ($ratios['restructured'] == 0) $ratios['restructured'] = 0.7056;

        return [
            'año' => $year,
            'mes' => $month,
            'portfolio' => $portfolioBalances,
            'provisions' => $provisionBalances,
            'ratios' => $ratios
        ];
    }

    /**
     * Suma los saldos de un conjunto de códigos para un período en históricos.
     */
    private function sumCodes($year, $month, array $codes)
    {
        return (float) FinancialHistory::where('año', $year)
            ->where('mes', $month)
            ->whereIn('codigo', $codes)
            ->sum('saldo');
    }

    /**
     * Simula la proyección de provisiones y gastos mes a mes para el año de presupuesto.
     */
    public function simulate(array $ratios, $budgetYear)
    {
        $histData = $this->calculateHistoricalData();
        $initialProvisions = $histData['provisions'];

        $results = [];

        // Definir códigos para cartera por segmento
        $segmentCodes = [
            'productive' => ['1401', '1449', '1425'],
            'consumer' => ['1402', '1426', '1450'],
            'microcredit' => ['1404', '1428', '1452'],
            'refinanced' => ['1410', '1412', '1434', '1436', '1458', '1460'],
            'restructured' => ['1418', '1420', '1442', '1444', '1466', '1468'],
        ];

        // Obtener saldos proyectados de cartera en master_budget_records agrupados por mes
        $portfolioProjected = [];
        for ($m = 1; $m <= 12; $m++) {
            $portfolioProjected[$m] = [];
            foreach ($segmentCodes as $segment => $codes) {
                $portfolioProjected[$m][$segment] = (float) MasterBudgetRecord::where('año', $budgetYear)
                    ->where('mes', $m)
                    ->whereIn('codigo', $codes)
                    ->sum('saldo');
            }
        }

        // Saldos acumulados iniciales (Diciembre año anterior)
        $prevProvisions = [
            'productive' => $initialProvisions['productive'] ?? 4698.86,
            'consumer' => $initialProvisions['consumer'] ?? 514259.37,
            'microcredit' => $initialProvisions['microcredit'] ?? 224495.77,
            'refinanced' => $initialProvisions['refinanced'] ?? 19986.80,
            'restructured' => $initialProvisions['restructured'] ?? 15798.23,
        ];

        for ($m = 1; $m <= 12; $m++) {
            $monthData = [
                'mes' => $m,
                'portfolio' => $portfolioProjected[$m],
                'provisions_acum' => [],
                'provision_gasto' => [],
            ];

            foreach ($ratios as $segment => $ratio) {
                // Provisión Acumulada Proyectada (Absoluta)
                $cartera = $portfolioProjected[$m][$segment];
                $acum = $cartera * $ratio;
                $monthData['provisions_acum'][$segment] = $acum;

                // Gasto Provisión Mensual
                $prev = $prevProvisions[$segment];
                $gasto = $acum - $prev;
                $monthData['provision_gasto'][$segment] = $gasto;

                // Actualizar para el siguiente mes
                $prevProvisions[$segment] = $acum;
            }

            // Cuentas fijas sin variación de provisiones
            $monthData['provisions_acum']['anticiclica'] = $initialProvisions['anticiclica'] ?? 29381.42;
            $monthData['provisions_acum']['noreversada'] = $initialProvisions['noreversada'] ?? 279690.67;
            $monthData['provisions_acum']['voluntaria'] = $initialProvisions['voluntaria'] ?? 2545.45;

            $results[$m] = $monthData;
        }

        return $results;
    }

    /**
     * Integra la simulación en master_budget_records y recalcula jerarquías.
     */
    public function integrateIntoBudget(array $simData, $budgetYear)
    {
        // 1. Cuentas hojas a insertar/actualizar
        $leafCodesMap = [
            '14990505' => ['name' => '(CARTERA DE CREDITO)', 'segment' => 'productive', 'type' => 'provision', 'account_type' => 'ESF', 'nivel' => 8],
            '14991005' => ['name' => '(CARTERA DE CREDITO)', 'segment' => 'consumer', 'type' => 'provision', 'account_type' => 'ESF', 'nivel' => 8],
            '14991505' => ['name' => '(CARTERA DE CRÉDITO INMOBILIARIO)', 'segment' => 'inmobiliario', 'type' => 'provision_zero', 'account_type' => 'ESF', 'nivel' => 8],
            '14992005' => ['name' => '(PROVISION CARTERA MICROCREDITOS)', 'segment' => 'microcredit', 'type' => 'provision', 'account_type' => 'ESF', 'nivel' => 8],
            '149945'   => ['name' => '(CARTERA DE CREDITO REFINANCIADA)', 'segment' => 'refinanced', 'type' => 'provision', 'account_type' => 'ESF', 'nivel' => 6],
            '149950'   => ['name' => '(CARTERA DE CREDITO REESTRUCTURADA)', 'segment' => 'restructured', 'type' => 'provision', 'account_type' => 'ESF', 'nivel' => 6],
            '149985'   => ['name' => '(PROVISION ANTICICLICA)', 'segment' => 'anticiclica', 'type' => 'provision_fixed', 'account_type' => 'ESF', 'nivel' => 6],
            '149987'   => ['name' => '(PROVISION NO REVERSADAS POR REQUER. NORMATIVO)', 'segment' => 'noreversada', 'type' => 'provision_fixed', 'account_type' => 'ESF', 'nivel' => 6],
            '149989'   => ['name' => '(PROVISION GENERICA VOLUNTARIA)', 'segment' => 'voluntaria', 'type' => 'provision_fixed', 'account_type' => 'ESF', 'nivel' => 6],
            
            '440210'   => ['name' => 'Crédito Productivo', 'segment' => 'productive', 'type' => 'gasto', 'account_type' => 'Estado de Pérdidas y Ganancias', 'nivel' => 6],
            '440220'   => ['name' => 'Crédito de consumo', 'segment' => 'consumer', 'type' => 'gasto', 'account_type' => 'Estado de Pérdidas y Ganancias', 'nivel' => 6],
            '440240'   => ['name' => 'Microcrédito', 'segment' => 'microcredit', 'type' => 'gasto', 'account_type' => 'Estado de Pérdidas y Ganancias', 'nivel' => 6],
        ];

        DB::transaction(function() use ($simData, $budgetYear, $leafCodesMap) {
            $now = now();

            // Eliminar registros existentes para estos códigos de hoja y también para sus cuentas padres asociadas
            // que serán re-calculadas por el rollup
            $targetCodes = array_keys($leafCodesMap);
            // También agregamos las cuentas padres directas para limpiarlas e impedir duplicados antes del Rollup
            $parentCodesToClean = ['149905', '149910', '149920', '1499', '4402', '44']; 
            
            MasterBudgetRecord::where('año', $budgetYear)
                ->whereIn('codigo', array_merge($targetCodes, $parentCodesToClean))
                ->delete();

            // Preparar lote de inserción
            $insertBatch = [];

            for ($m = 1; $m <= 12; $m++) {
                $monthSim = $simData[$m];

                foreach ($leafCodesMap as $code => $meta) {
                    $saldo = 0;
                    if ($meta['type'] === 'provision') {
                        // Provisiones son saldos negativos en el Activo
                        $saldo = -abs($monthSim['provisions_acum'][$meta['segment']]);
                    } elseif ($meta['type'] === 'provision_fixed') {
                        $saldo = -abs($monthSim['provisions_acum'][$meta['segment']]);
                    } elseif ($meta['type'] === 'provision_zero') {
                        $saldo = 0.00;
                    } elseif ($meta['type'] === 'gasto') {
                        // Gastos por provisiones son positivos/negativos en Resultados
                        $saldo = (float) $monthSim['provision_gasto'][$meta['segment']];
                    }

                    $insertBatch[] = [
                        'nivel' => $meta['nivel'],
                        'tipo' => $meta['account_type'],
                        'codigo' => $code,
                        'cuenta' => $meta['name'],
                        'mes' => $m,
                        'año' => $budgetYear,
                        'saldo' => $saldo,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Insertar registros hojas
            $chunks = array_chunk($insertBatch, 500);
            foreach ($chunks as $chunk) {
                MasterBudgetRecord::insert($chunk);
            }

            // 2. Ejecutar el Rollup contable para actualizar los padres agregados
            $this->runHierarchyRollup($budgetYear);
        });
    }

    /**
     * Recalcula la jerarquía de cuentas contables de abajo hacia arriba en master_budget_records.
     */
    private function runHierarchyRollup($budgetYear)
    {
        // 1. Obtener la lista única de cuentas del catálogo desde históricos (catálogo base)
        $allAccounts = FinancialHistory::select('codigo', 'cuenta', 'nivel', 'tipo')
            ->distinct()
            ->orderBy('codigo', 'asc')
            ->get()
            ->toArray();

        // 2. Determinar cuáles son padres
        $parentCodes = [];
        for ($i = 0; $i < count($allAccounts) - 1; $i++) {
            $currentCode = (string)$allAccounts[$i]['codigo'];
            $nextCode = (string)$allAccounts[$i+1]['codigo'];
            if ($currentCode !== '' && str_starts_with($nextCode, $currentCode)) {
                $parentCodes[$currentCode] = true;
            }
        }

        // 3. Procesar mes por mes
        $now = now();
        for ($m = 1; $m <= 12; $m++) {
            // Cargar saldos reales del mes para todas las cuentas en la tabla de presupuestos
            $monthRecords = MasterBudgetRecord::where('año', $budgetYear)
                ->where('mes', $m)
                ->get()
                ->keyBy('codigo');

            // Preparar el array de balances mensuales para calcular de abajo hacia arriba
            $monthBalances = [];
            foreach ($monthRecords as $code => $rec) {
                $monthBalances[$code] = (float)$rec->saldo;
            }

            // Ordenar por longitud de código descendente para acumular de abajo hacia arriba
            $sortedAccounts = $allAccounts;
            usort($sortedAccounts, function($a, $b) {
                return strlen($b['codigo']) <=> strlen($a['codigo']);
            });

            foreach ($sortedAccounts as $acc) {
                $code = $acc['codigo'];
                if (isset($parentCodes[$code])) {
                    // Es un padre. Sumamos todos sus hijos hoja directos
                    $sum = 0;
                    foreach ($allAccounts as $child) {
                        $childCode = $child['codigo'];
                        // Suma todas las hojas que empiecen con este prefijo de padre
                        if (!isset($parentCodes[$childCode]) && str_starts_with($childCode, $code)) {
                            $sum += ($monthBalances[$childCode] ?? 0);
                        }
                    }
                    $monthBalances[$code] = $sum;
                }
            }

            // Guardar o actualizar todos los padres (y hojas modificadas) en la DB
            foreach ($allAccounts as $acc) {
                $code = $acc['codigo'];
                $val = $monthBalances[$code] ?? 0;
                
                $isParent = isset($parentCodes[$code]);
                $rec = $monthRecords->get($code);
                
                if ($rec) {
                    $rec->saldo = $val;
                    $rec->save();
                } elseif ($isParent || $val != 0) {
                    MasterBudgetRecord::create([
                        'nivel' => $acc['nivel'],
                        'tipo' => $acc['tipo'],
                        'codigo' => $code,
                        'cuenta' => $acc['cuenta'],
                        'mes' => $m,
                        'año' => $budgetYear,
                        'saldo' => $val,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
