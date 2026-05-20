<?php

namespace App\Livewire;

use App\Models\FinancialHistory;
use Livewire\Component;

class HistoricalPortfolio extends Component
{
    public $selectedYear = '';
    public $trend = [];
    
    public function mount()
    {
        $latestPeriod = FinancialHistory::select('año')
            ->distinct()
            ->orderBy('año', 'desc')
            ->first();
            
        if ($latestPeriod) {
            $this->selectedYear = $latestPeriod->año;
        } else {
            $this->selectedYear = date('Y');
        }
        
        $this->calculateTrend();
    }

    public function updatedSelectedYear()
    {
        $this->calculateTrend();
    }

    public function calculateTrend()
    {
        $year = $this->selectedYear;
        
        $records = FinancialHistory::where('año', $year)
            ->where('codigo', 'like', '14%')
            ->get();

        $months = range(1, 12);
        $categories = [
            'productive' => 'Crédito Productivo',
            'consumer' => 'Crédito de Consumo',
            'microcredit' => 'Microcrédito',
            'inmobiliario' => 'Inmobiliario',
            'consumer_refinanced' => 'Consumo Refinanciado',
            'microcredit_refinanced' => 'Microcrédito Refinanciado',
            'consumer_restructured' => 'Consumo Reestructurado',
            'microcredit_restructured' => 'Microcrédito Reestructurado',
            'covid_refinanced' => 'Refinanciado COVID-19',
            'covid_restructured' => 'Reestructurado COVID-19',
            'provisiones' => 'Provisiones para Créditos Incobrables'
        ];

        $data = [];
        foreach ($categories as $key => $label) {
            $data[$key] = [
                'label' => $label,
                'monthly' => array_fill(1, 12, 0.0),
                'total' => 0.0
            ];
        }

        // Agrupados adicionales
        $totals = [
            'bruta' => array_fill(1, 12, 0.0),
            'neta' => array_fill(1, 12, 0.0),
            'vencer' => array_fill(1, 12, 0.0),
            'riesgo' => array_fill(1, 12, 0.0),
            'total_bruta' => 0.0,
            'total_neta' => 0.0,
            'total_vencer' => 0.0,
            'total_riesgo' => 0.0
        ];

        // Solo sumamos hojas (código de longitud >= 6) y excluimos las subcuentas de provisiones (1499) para evitar doble conteo
        $leafRecords = $records->filter(function($r) {
            return strlen($r->codigo) >= 6 && !str_starts_with($r->codigo, '1499');
        });

        foreach ($leafRecords as $r) {
            $m = (int)$r->mes;
            if ($m < 1 || $m > 12) continue;

            $segment = $this->getSegmentName($r->codigo);
            if ($segment) {
                $val = (float)$r->saldo;
                $data[$segment]['monthly'][$m] += $val;
            }

            // Clasificación por vencer / en riesgo
            $code = (string)$r->codigo;
            $isVencer = str_starts_with($code, '1401') || str_starts_with($code, '1402') || str_starts_with($code, '1404') ||
                        str_starts_with($code, '1410') || str_starts_with($code, '1412') || str_starts_with($code, '1418') ||
                        str_starts_with($code, '1420') || str_starts_with($code, '149105') || str_starts_with($code, '149120') ||
                        str_starts_with($code, '149420') || str_starts_with($code, '149440');
                        
            $isRiesgo = str_starts_with($code, '1425') || str_starts_with($code, '1426') || str_starts_with($code, '1428') ||
                        str_starts_with($code, '1434') || str_starts_with($code, '1436') || str_starts_with($code, '1442') ||
                        str_starts_with($code, '1444') || str_starts_with($code, '1449') || str_starts_with($code, '1450') ||
                        str_starts_with($code, '1451') || str_starts_with($code, '1452') || str_starts_with($code, '1458') ||
                        str_starts_with($code, '1460') || str_starts_with($code, '1466') || str_starts_with($code, '1468') ||
                        str_starts_with($code, '149220') || str_starts_with($code, '149320') || str_starts_with($code, '149520') ||
                        str_starts_with($code, '149540') || str_starts_with($code, '149620') || str_starts_with($code, '149640');

            if ($isVencer) {
                $totals['vencer'][$m] += (float)$r->saldo;
            }
            if ($isRiesgo) {
                $totals['riesgo'][$m] += (float)$r->saldo;
            }
        }

        // Cargar provisiones directamente desde la cuenta principal 1499 para alinearse con la Fila 195 de EEFFun
        $provisionRecords = $records->filter(function($r) {
            return (string)$r->codigo === '1499';
        });

        foreach ($provisionRecords as $r) {
            $m = (int)$r->mes;
            if ($m >= 1 && $m <= 12) {
                $data['provisiones']['monthly'][$m] = (float)$r->saldo;
            }
        }

        // Calcular totales de las filas y consolidar Cartera Bruta / Neta
        foreach ($data as $key => &$row) {
            $row['total'] = array_sum($row['monthly']);
            
            // Sumar a cartera bruta si no es la cuenta de provisiones (1499)
            if ($key !== 'provisiones') {
                foreach ($months as $m) {
                    $totals['bruta'][$m] += $row['monthly'][$m];
                }
            }
        }
        unset($row);

        // Consolidar neta y calcular sumas anuales de totales
        foreach ($months as $m) {
            $totals['neta'][$m] = $totals['bruta'][$m] + $data['provisiones']['monthly'][$m]; // provisión es negativa
        }

        $totals['total_bruta'] = array_sum($totals['bruta']);
        $totals['total_neta'] = array_sum($totals['neta']);
        $totals['total_vencer'] = array_sum($totals['vencer']);
        $totals['total_riesgo'] = array_sum($totals['riesgo']);

        // Calcular promedio dinámico según la cantidad de meses que tengan información registrada
        $monthsWithData = $records->pluck('mes')->unique()->filter(function($m) {
            return $m >= 1 && $m <= 12;
        })->toArray();
        $monthCount = count($monthsWithData);
        if ($monthCount <= 0) {
            $monthCount = 1;
        }

        $averages = [
            'bruta' => array_sum($totals['bruta']) / $monthCount,
            'provisiones' => array_sum($data['provisiones']['monthly']) / $monthCount,
            'vencer' => array_sum($totals['vencer']) / $monthCount,
            'riesgo' => array_sum($totals['riesgo']) / $monthCount,
        ];

        $this->trend = [
            'segments' => $data,
            'totals' => $totals,
            'averages' => $averages,
            'month_count' => $monthCount
        ];
    }

    private function getSegmentName($code)
    {
        $code = (string)$code;
        
        if (str_starts_with($code, '1499')) {
            return 'provisiones';
        }
        
        if (str_starts_with($code, '1491') || str_starts_with($code, '1492') || str_starts_with($code, '1493')) {
            return 'covid_refinanced';
        }
        
        if (str_starts_with($code, '1494') || str_starts_with($code, '1495') || str_starts_with($code, '1496')) {
            return 'covid_restructured';
        }
        
        if (str_starts_with($code, '1420') || str_starts_with($code, '1444') || str_starts_with($code, '1468')) {
            return 'microcredit_restructured';
        }
        
        if (str_starts_with($code, '1418') || str_starts_with($code, '1442') || str_starts_with($code, '1466')) {
            return 'consumer_restructured';
        }
        
        if (str_starts_with($code, '1412') || str_starts_with($code, '1436') || str_starts_with($code, '1460')) {
            return 'microcredit_refinanced';
        }
        
        if (str_starts_with($code, '1410') || str_starts_with($code, '1434') || str_starts_with($code, '1458')) {
            return 'consumer_refinanced';
        }
        
        if (str_starts_with($code, '1451')) {
            return 'inmobiliario';
        }
        
        if (str_starts_with($code, '1404') || str_starts_with($code, '1428') || str_starts_with($code, '1452')) {
            return 'microcredit';
        }
        
        if (str_starts_with($code, '1402') || str_starts_with($code, '1426') || str_starts_with($code, '1450')) {
            return 'consumer';
        }
        
        if (str_starts_with($code, '1401') || str_starts_with($code, '1425') || str_starts_with($code, '1449')) {
            return 'productive';
        }
        
        return null;
    }

    public function render()
    {
        $availableYears = FinancialHistory::select('año')
            ->distinct()
            ->orderBy('año', 'desc')
            ->pluck('año');

        return view('livewire.historical-portfolio', [
            'availableYears' => $availableYears,
            'trend' => $this->trend
        ])->layout('layouts.ledger');
    }
}
