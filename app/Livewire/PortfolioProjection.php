<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Projection;
use App\Models\ProjectionParameter;

class PortfolioProjection extends Component
{
    // Crecimiento
    public $targetGrowthProductive = 0.00;
    public $targetGrowthConsumer = 0.62;
    public $targetGrowthMicrocredit = 0.20;
    public $targetGrowthRefinanced = 0.00;
    public $targetGrowthRestructured = 0.00;

    // Recuperaciones
    public $recoveryProductive = 232150.98;
    public $recoveryConsumer = 12559019.26;
    public $recoveryMicrocredit = 2914290.49;
    public $recoveryRefinanced = 99983.89;
    public $recoveryRestructured = 138290.52;

    public $saved = false;

    public function mount()
    {
        // Cargar la última proyección si existe
        $lastProjection = Projection::with('parameters')->latest()->first();
        if ($lastProjection && $lastProjection->parameters) {
            $params = $lastProjection->parameters;
            $this->targetGrowthProductive = $params->target_growth_productive;
            $this->targetGrowthConsumer = $params->target_growth_consumer;
            $this->targetGrowthMicrocredit = $params->target_growth_microcredit;
            $this->targetGrowthRefinanced = $params->target_growth_refinanced;
            $this->targetGrowthRestructured = $params->target_growth_restructured;

            $this->recoveryProductive = $params->recovery_productive;
            $this->recoveryConsumer = $params->recovery_consumer;
            $this->recoveryMicrocredit = $params->recovery_microcredit;
            $this->recoveryRefinanced = $params->recovery_refinanced;
            $this->recoveryRestructured = $params->recovery_restructured;
        }
    }

    public function getColocacionRequeridaProperty()
    {
        $saldosIniciales = [
            'productive'   => 5000000,
            'consumer'     => 3000000,
            'microcredit'  => 1000000,
            'refinanced'   => 500000,
            'restructured' => 200000,
        ];

        return [
            'productive' => ($saldosIniciales['productive'] * $this->targetGrowthProductive) + $this->recoveryProductive,
            'consumer' => ($saldosIniciales['consumer'] * $this->targetGrowthConsumer) + $this->recoveryConsumer,
            'microcredit' => ($saldosIniciales['microcredit'] * $this->targetGrowthMicrocredit) + $this->recoveryMicrocredit,
            'refinanced' => ($saldosIniciales['refinanced'] * $this->targetGrowthRefinanced) + $this->recoveryRefinanced,
            'restructured' => ($saldosIniciales['restructured'] * $this->targetGrowthRestructured) + $this->recoveryRestructured,
        ];
    }

    public function save()
    {
        $projection = Projection::latest()->first();
        if (!$projection) {
            $projection = Projection::create([
                'name' => 'Proyección Base 2026',
                'description' => 'Generada desde la interfaz web'
            ]);
        }

        $paramsData = [
            'target_growth_productive' => $this->targetGrowthProductive,
            'target_growth_consumer' => $this->targetGrowthConsumer,
            'target_growth_microcredit' => $this->targetGrowthMicrocredit,
            'target_growth_refinanced' => $this->targetGrowthRefinanced,
            'target_growth_restructured' => $this->targetGrowthRestructured,

            'recovery_productive' => $this->recoveryProductive,
            'recovery_consumer' => $this->recoveryConsumer,
            'recovery_microcredit' => $this->recoveryMicrocredit,
            'recovery_refinanced' => $this->recoveryRefinanced,
            'recovery_restructured' => $this->recoveryRestructured,
        ];

        if ($projection->parameters) {
            $projection->parameters->update($paramsData);
        } else {
            $projection->parameters()->create($paramsData);
        }

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.portfolio-projection')->layout('layouts.ledger');
    }
}
