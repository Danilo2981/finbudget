<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FinancialHistory;
use App\Models\Projection;

class ProyCartCre extends Component
{
    public array $rows        = [];
    public array $totals      = [];
    public int   $budgetYear;
    public int   $baseYear;
    public int   $baseMes;

    private array $segmentCodes = [
        'Productivo'     => ['1401', '1449', '1425'],
        'Consumo'        => ['1402', '1426', '1450'],
        'Microcrédito'   => ['1404', '1428', '1452'],
        'Refinanciada'   => ['1410', '1412', '1434', '1436', '1458', '1460'],
        'Reestructurada' => ['1418', '1420', '1442', '1444', '1466', '1468'],
    ];

    private array $paramKeys = [
        'Productivo'     => 'productive',
        'Consumo'        => 'consumer',
        'Microcrédito'   => 'microcredit',
        'Refinanciada'   => 'refinanced',
        'Reestructurada' => 'restructured',
    ];

    public function mount(): void
    {
        $latest = FinancialHistory::select('año', 'mes')
            ->orderByDesc('año')->orderByDesc('mes')->first();

        $this->baseYear   = $latest?->año   ?? (int)date('Y') - 1;
        $this->baseMes    = $latest?->mes    ?? 12;
        $this->budgetYear = $this->baseYear + 1;

        $this->buildTable();
    }

    private function sumCodes(int $year, int $month, array $codes): float
    {
        return (float) FinancialHistory::where('año', $year)
            ->where('mes', $month)
            ->whereIn('codigo', $codes)
            ->sum('saldo');
    }

    private function countNegMonths(int $year, array $codes): int
    {
        // Count months (within $year) where portfolio balance decreased from prior month
        $balances = [];
        for ($m = 1; $m <= 12; $m++) {
            $v = $this->sumCodes($year, $m, $codes);
            if ($v != 0.0) {
                $balances[$m] = $v;
            }
        }

        $neg = 0;
        $keys = array_keys($balances);
        for ($i = 1; $i < count($keys); $i++) {
            if ($balances[$keys[$i]] < $balances[$keys[$i - 1]]) {
                $neg++;
            }
        }
        return $neg;
    }

    private function buildTable(): void
    {
        $projection = Projection::where('year', $this->budgetYear)
            ->with('parameters')->latest()->first();
        $params = $projection?->parameters;

        $growthRates = [
            'Productivo'     => (float)($params?->target_growth_productive   ?? 0),
            'Consumo'        => (float)($params?->target_growth_consumer      ?? 0.62),
            'Microcrédito'   => (float)($params?->target_growth_microcredit   ?? 0.20),
            'Refinanciada'   => (float)($params?->target_growth_refinanced    ?? 0),
            'Reestructurada' => (float)($params?->target_growth_restructured  ?? 0),
        ];

        $recoveries = [
            'Productivo'     => (float)($params?->recovery_productive   ?? 0),
            'Consumo'        => (float)($params?->recovery_consumer      ?? 0),
            'Microcrédito'   => (float)($params?->recovery_microcredit   ?? 0),
            'Refinanciada'   => (float)($params?->recovery_refinanced    ?? 0),
            'Reestructurada' => (float)($params?->recovery_restructured  ?? 0),
        ];

        // Productividad periódica (misma fórmula que PortfolioProjection)
        $autoCredits = (int)($params?->auto_credits_per_exec  ?? 15);
        $autoExecs   = (int)($params?->auto_exec_count        ?? 27);
        $autoAvg     = (float)($params?->auto_avg_credit_value ?? 18000);
        $autoMonths  = (int)($params?->auto_months            ?? 12);
        $autoTotal   = $autoCredits * $autoExecs * $autoAvg * $autoMonths;

        // Months from base period to December of base year
        $monthsToDecember = 12 - $this->baseMes;

        // Base saldos for all segments
        $saldos = [];
        foreach ($this->segmentCodes as $label => $codes) {
            $saldos[$label] = $this->sumCodes($this->baseYear, $this->baseMes, $codes);
        }
        $totalSaldo = array_sum($saldos);

        // Projected Dec 2026 saldos
        $saldosProyect = [];
        foreach ($this->segmentCodes as $label => $codes) {
            $base  = $saldos[$label];
            $rate  = $growthRates[$label];
            $delta = $base * ($rate / 12);
            // Dic del año presupuesto = base + (meses hasta dic base + 12) × delta
            $saldosProyect[$label] = $base + ($monthsToDecember + 12) * $delta;
        }
        $totalProyect = array_sum($saldosProyect);

        $rows = [];
        foreach ($this->segmentCodes as $label => $codes) {
            $saldo      = $saldos[$label];
            $rate       = $growthRates[$label];
            $recup      = $recoveries[$label];

            // Colocación adicional = productividad periódica × porcentaje (igual que Parámetros de Proyección)
            $colocAdicional = $autoTotal * $rate;

            // Colocación requerida con recup cartera corte = recup existente + adicional
            $colocReqCorte = $recup + $colocAdicional;

            // Colocación requerida con recup proyectada = idéntica si no hay parámetro separado para recup colocación
            // (columna 4 = RECUPER PROYECT COLOCACION se deja en 0 hasta definir fuente)
            $recupColocacion = 0.0;
            $colocReqProyect = $recup + $recupColocacion + $colocAdicional;

            // Cartera originada, vendida y administrada = colocación requerida (mejor proxy disponible)
            $cartOriginada = $colocReqCorte;

            $negMes = $this->countNegMonths($this->baseYear, $codes);

            $rows[$label] = [
                'label'             => $label,
                'saldo'             => $saldo,
                'particip'          => $totalSaldo > 0 ? ($saldo / $totalSaldo * 100) : 0,
                'recup_cartera'     => $recup,
                'recup_colocacion'  => $recupColocacion,
                'coloc_adicional'   => $colocAdicional,
                'coloc_req_corte'   => $colocReqCorte,
                'coloc_req_proyect' => $colocReqProyect,
                'cart_originada'    => $cartOriginada,
                'saldo_proyect'     => $saldosProyect[$label],
                'particip_proyect'  => $totalProyect > 0 ? ($saldosProyect[$label] / $totalProyect * 100) : 0,
                'comportamiento'    => $rate * 100,
                'neg_mes'           => $negMes,
                'neg_mes_bi'        => 0,
                'neg_mes_bc'        => 0,
            ];
        }

        $this->rows = array_values($rows);

        $this->totals = [
            'saldo'             => $totalSaldo,
            'particip'          => 100.0,
            'recup_cartera'     => array_sum(array_column($rows, 'recup_cartera')),
            'recup_colocacion'  => 0.0,
            'coloc_adicional'   => array_sum(array_column($rows, 'coloc_adicional')),
            'coloc_req_corte'   => array_sum(array_column($rows, 'coloc_req_corte')),
            'coloc_req_proyect' => array_sum(array_column($rows, 'coloc_req_proyect')),
            'cart_originada'    => array_sum(array_column($rows, 'cart_originada')),
            'saldo_proyect'     => $totalProyect,
            'particip_proyect'  => 100.0,
            'comportamiento'    => null,
            'neg_mes'           => array_sum(array_column($rows, 'neg_mes')),
            'neg_mes_bi'        => 0,
            'neg_mes_bc'        => 0,
        ];
    }

    public function render()
    {
        return view('livewire.proy-cart-cre')->layout('layouts.ledger');
    }
}
