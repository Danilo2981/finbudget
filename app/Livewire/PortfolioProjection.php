<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FinancialHistory;
use App\Models\Projection;

class PortfolioProjection extends Component
{
    public float $targetGrowthProductive   = 0.00;
    public float $targetGrowthConsumer     = 0.62;
    public float $targetGrowthMicrocredit  = 0.20;
    public float $targetGrowthRefinanced   = 0.00;
    public float $targetGrowthRestructured = 0.00;

    // Volumen colocación automotriz
    public int   $autoCreditsPerExec  = 15;
    public int   $autoExecCount       = 27;
    public float $autoAvgCreditValue  = 18000.00;
    public int   $autoMonths          = 12;

    public int   $baseYear;
    public int   $baseMes;
    public int   $budgetYear;

    private array $segmentCodes = [
        'productive'   => ['1401', '1449', '1425'],
        'consumer'     => ['1402', '1426', '1450'],
        'microcredit'  => ['1404', '1428', '1452'],
        'refinanced'   => ['1410', '1412', '1434', '1436', '1458', '1460'],
        'restructured' => ['1418', '1420', '1442', '1444', '1466', '1468'],
    ];

    public function mount(): void
    {
        $latest = FinancialHistory::select('año', 'mes')
            ->orderByDesc('año')->orderByDesc('mes')->first();

        $this->baseYear   = $latest?->año   ?? (int)date('Y') - 1;
        $this->baseMes    = $latest?->mes    ?? 12;
        $this->budgetYear = $this->baseYear + 1;

        $lastProjection = Projection::where('year', $this->budgetYear)
            ->with('parameters')->latest()->first();

        if ($lastProjection?->parameters) {
            $p = $lastProjection->parameters;
            $this->targetGrowthProductive   = (float) $p->target_growth_productive;
            $this->targetGrowthConsumer     = (float) $p->target_growth_consumer;
            $this->targetGrowthMicrocredit  = (float) $p->target_growth_microcredit;
            $this->targetGrowthRefinanced   = (float) $p->target_growth_refinanced;
            $this->targetGrowthRestructured = (float) $p->target_growth_restructured;

            $this->autoCreditsPerExec = (int)   $p->auto_credits_per_exec;
            $this->autoExecCount      = (int)   $p->auto_exec_count;
            $this->autoAvgCreditValue = (float) $p->auto_avg_credit_value;
            $this->autoMonths         = (int)   $p->auto_months;
        }
    }

    public function getSaldosProperty(): array
    {
        $saldos = [];
        foreach ($this->segmentCodes as $key => $codes) {
            $saldos[$key] = (float) FinancialHistory::where('año', $this->baseYear)
                ->where('mes', $this->baseMes)
                ->whereIn('codigo', $codes)
                ->sum('saldo');
        }
        return $saldos;
    }

    public function getAutoVolumeProperty(): array
    {
        $productividad = $this->autoCreditsPerExec * $this->autoExecCount * $this->autoAvgCreditValue;
        $total         = $productividad * $this->autoMonths;
        $totalOps      = $this->autoAvgCreditValue > 0 ? round($total / $this->autoAvgCreditValue) : 0;
        $avgMonthlyOps = ($this->autoAvgCreditValue > 0 && $this->autoMonths > 0)
            ? round($total / $this->autoAvgCreditValue / $this->autoMonths)
            : 0;

        return compact('productividad', 'total', 'totalOps', 'avgMonthlyOps');
    }

    public function getRowsProperty(): array
    {
        $productividad = $this->autoVolume['productividad'];

        $rates = [
            'productive'   => $this->targetGrowthProductive,
            'consumer'     => $this->targetGrowthConsumer,
            'microcredit'  => $this->targetGrowthMicrocredit,
            'refinanced'   => $this->targetGrowthRefinanced,
            'restructured' => $this->targetGrowthRestructured,
        ];

        $labels = [
            'productive'   => 'Productivo',
            'consumer'     => 'Consumo',
            'microcredit'  => 'Microcrédito',
            'refinanced'   => 'Refinanciada',
            'restructured' => 'Reestructurada',
        ];

        $total = $this->autoVolume['total'];   // productividad × meses

        $rows = [];
        foreach ($labels as $key => $label) {
            $rate  = $rates[$key];
            $rows[] = [
                'key'             => $key,
                'label'           => $label,
                'productividad'   => $total,
                'rate'            => $rate,
                'coloc_adicional' => $total * $rate,
            ];
        }
        return $rows;
    }

    public function save(): void
    {
        $projection = Projection::where('year', $this->budgetYear)->latest()->first();
        if (!$projection) {
            $projection = Projection::create([
                'name' => 'Proyección Base ' . $this->budgetYear,
                'year' => $this->budgetYear,
            ]);
        }

        $data = [
            'target_growth_productive'   => $this->targetGrowthProductive,
            'target_growth_consumer'     => $this->targetGrowthConsumer,
            'target_growth_microcredit'  => $this->targetGrowthMicrocredit,
            'target_growth_refinanced'   => $this->targetGrowthRefinanced,
            'target_growth_restructured' => $this->targetGrowthRestructured,
            'auto_credits_per_exec'      => $this->autoCreditsPerExec,
            'auto_exec_count'            => $this->autoExecCount,
            'auto_avg_credit_value'      => $this->autoAvgCreditValue,
            'auto_months'                => $this->autoMonths,
        ];

        if ($projection->parameters) {
            $projection->parameters->update($data);
        } else {
            $projection->parameters()->create($data);
        }

        $this->redirect(route('proy-cart-cre'), navigate: true);
    }

    public function render()
    {
        return view('livewire.portfolio-projection')->layout('layouts.ledger');
    }
}
