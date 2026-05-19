<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Projection;
use App\Services\BudgetEngine;

class BudgetSimulator extends Component
{
    // Parámetros de la simulación
    public $placementProductive = 232150.98;
    public $placementConsumer = 66796619.26;
    public $placementMicrocredit = 20410290.49;
    public $placementRefinanced = 110815.48;
    public $placementRestructured = 43105.10;

    public $sightGrowth = 0.015;
    public $termGrowth = 0.02;
    
    public $productiveRate = 0.0986;
    public $consumerRate = 0.15;
    public $microcreditRate = 0.22;
    public $sightRate = 0.025;
    public $termRate = 0.08;
    
    // Recuperaciones Extra (Estado de Resultados)
    public $recoveryWrittenOff = 12000;
    public $reversalProvisions = 24000;

    public $techInvestment = 10000;
    public $imageInvestment = 4166;
    public $operatingExpenses = 150000;

    public $results = null;

    public function calculate()
    {
        // Cargar la última proyección guardada en la base de datos (Fase 1)
        $projection = Projection::with('parameters')->latest()->first();
        
        if (!$projection || !$projection->parameters) {
            session()->flash('error', 'Debes guardar primero la Proyección de Cartera.');
            return;
        }

        // Actualizar parámetros de tasas y gastos que el usuario ingresó en esta pantalla
        $projection->parameters->update([
            'placement_productive' => $this->placementProductive,
            'placement_consumer' => $this->placementConsumer,
            'placement_microcredit' => $this->placementMicrocredit,
            'placement_refinanced' => $this->placementRefinanced,
            'placement_restructured' => $this->placementRestructured,

            'sight_deposit_growth_rate' => $this->sightGrowth,
            'term_deposit_growth_rate' => $this->termGrowth,
            
            'productive_interest_rate' => $this->productiveRate,
            'consumer_interest_rate' => $this->consumerRate,
            'microcredit_interest_rate' => $this->microcreditRate,
            'sight_deposit_interest_rate' => $this->sightRate,
            'term_deposit_interest_rate' => $this->termRate,

            'recovery_written_off' => $this->recoveryWrittenOff,
            'reversal_provisions' => $this->reversalProvisions,

            'tech_investment' => $this->techInvestment,
            'image_investment' => $this->imageInvestment,
            'operating_expenses' => $this->operatingExpenses
        ]);

        $engine = new BudgetEngine();
        $this->results = $engine->calculate($projection);
    }

    public function render()
    {
        return view('livewire.budget-simulator')->layout('layouts.ledger');
    }
}
