<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MasterBudgetRecord;
use App\Models\FinancialHistory;
use Illuminate\Support\Facades\DB;

class MasterBudget extends Component
{
    public $selectedMonth = 'all';
    public $search = '';
    public $isGenerating = false;
    public $showMathModal = false;
    public $budgetYear = null;
    
    public function mount()
    {
        $latestYear = FinancialHistory::max('año');
        $this->budgetYear = $latestYear ? $latestYear + 1 : (int)date('Y') + 1;
    }
    
    public function generateBudget()
    {
        $this->isGenerating = true;
        
        // 1. Clear existing budget
        MasterBudgetRecord::truncate();
        
        // 2. Get the unique chart of accounts from historical data (latest year/month)
        $latestYear = FinancialHistory::max('año');
        if (!$latestYear) {
            session()->flash('error', 'No hay datos históricos para proyectar.');
            $this->isGenerating = false;
            return;
        }
        
        $this->budgetYear = $latestYear + 1;

        // We will build a linear regression for each leaf account code
        // Leaf nodes are those not used as prefixes by any other code
        $allCodesQuery = FinancialHistory::select('codigo', 'cuenta', 'nivel', 'tipo')
            ->distinct()
            ->orderBy('codigo', 'asc')
            ->get();
            
        $parentCodes = [];
        $allCodes = $allCodesQuery->toArray();
        for ($i = 0; $i < count($allCodes) - 1; $i++) {
            $currentCode = (string)$allCodes[$i]['codigo'];
            $nextCode = (string)$allCodes[$i+1]['codigo'];
            if ($currentCode !== '' && str_starts_with($nextCode, $currentCode)) {
                $parentCodes[$currentCode] = true;
            }
        }
        
        // Group historical data by code and month (for regression)
        $histories = FinancialHistory::select('codigo', 'año', 'mes', 'saldo')
            ->orderBy('año')
            ->orderBy('mes')
            ->get();
            
        $dataPoints = [];
        foreach ($histories as $h) {
            if (!isset($dataPoints[$h->codigo])) {
                $dataPoints[$h->codigo] = [];
            }
            // Create a continuous x index (e.g. months since 2020)
            $x = ($h->año - 2020) * 12 + $h->mes;
            $dataPoints[$h->codigo][] = ['x' => $x, 'y' => (float)$h->saldo];
        }

        $budgetData = [];
        $budgetYear = $this->budgetYear;
        
        // We will project leaf nodes first, then sum up parents later
        // But to make it simpler and maintain structure, we can project ALL codes.
        // Wait, projecting parents independently might break sum integrity.
        // Let's only project leaf nodes, and let parents be 0 initially.
        
        $leafForecasts = [];
        
        foreach ($allCodes as $acc) {
            $code = $acc['codigo'];
            $isParent = isset($parentCodes[$code]);
            
            if (!$isParent) {
                // Calculate Linear Regression for this leaf
                $points = $dataPoints[$code] ?? [];
                $slope = 0;
                $intercept = 0;
                $n = count($points);
                
                if ($n > 1) {
                    $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;
                    foreach ($points as $p) {
                        $sumX += $p['x'];
                        $sumY += $p['y'];
                        $sumXY += ($p['x'] * $p['y']);
                        $sumX2 += ($p['x'] * $p['x']);
                    }
                    $denominator = ($n * $sumX2) - ($sumX * $sumX);
                    if ($denominator != 0) {
                        $slope = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
                        $intercept = ($sumY - ($slope * $sumX)) / $n;
                    } else {
                        // Fallback to average if X doesn't change (rare)
                        $intercept = $sumY / $n;
                    }
                } elseif ($n == 1) {
                    $intercept = $points[0]['y'];
                }
                
                // Forecast for 12 months of 2026
                for ($m = 1; $m <= 12; $m++) {
                    $targetX = ($budgetYear - 2020) * 12 + $m;
                    $forecastY = ($slope * $targetX) + $intercept;
                    
                    // Don't allow negative balances for normally positive accounts if they were never negative?
                    // For now, just keep the raw statistical projection.
                    
                    if (!isset($leafForecasts[$m])) $leafForecasts[$m] = [];
                    $leafForecasts[$m][$code] = $forecastY;
                }
            }
        }
        
        // Now roll up the tree for each month
        $now = now();
        $insertBatch = [];
        
        for ($m = 1; $m <= 12; $m++) {
            // We need to calculate parents from bottom up.
            // Sort accounts by length of code descending (deepest first)
            $sortedCodes = $allCodes;
            usort($sortedCodes, function($a, $b) {
                return strlen($b['codigo']) <=> strlen($a['codigo']);
            });
            
            $monthBalances = $leafForecasts[$m] ?? [];
            
            foreach ($sortedCodes as $acc) {
                $code = $acc['codigo'];
                if (isset($parentCodes[$code])) {
                    // It's a parent. Sum immediate children.
                    $sum = 0;
                    foreach ($allCodes as $child) {
                        $childCode = $child['codigo'];
                        // Immediate child logic: starts with parent code, and length is exactly next level
                        // Wait, easier: sum ALL leaf nodes that start with this code
                        if (!isset($parentCodes[$childCode]) && str_starts_with($childCode, $code)) {
                            $sum += ($monthBalances[$childCode] ?? 0);
                        }
                    }
                    $monthBalances[$code] = $sum;
                }
            }
            
            // Prepare insert array
            foreach ($allCodes as $acc) {
                $insertBatch[] = [
                    'nivel' => $acc['nivel'],
                    'tipo' => $acc['tipo'],
                    'codigo' => $acc['codigo'],
                    'cuenta' => $acc['cuenta'],
                    'mes' => $m,
                    'año' => $budgetYear,
                    'saldo' => $monthBalances[$acc['codigo']] ?? 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        
        // Insert in chunks
        DB::transaction(function() use ($insertBatch) {
            $chunks = array_chunk($insertBatch, 500);
            foreach ($chunks as $chunk) {
                MasterBudgetRecord::insert($chunk);
            }
        });
        
        $this->isGenerating = false;
        session()->flash('message', "Presupuesto Maestro {$this->budgetYear} generado exitosamente usando proyecciones estadísticas.");
    }
    
    public function render()
    {
        $query = MasterBudgetRecord::query();
        
        if ($this->selectedMonth !== 'all') {
            $query->where('mes', $this->selectedMonth);
        }
        
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('cuenta', 'like', '%' . $this->search . '%')
                  ->orWhere('codigo', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->selectedMonth === 'all') {
            $records = $query->selectRaw('nivel, tipo, codigo, cuenta, SUM(saldo) as saldo')
                             ->groupBy('nivel', 'tipo', 'codigo', 'cuenta')
                             ->orderBy('codigo', 'asc')
                             ->get();
        } else {
            $records = $query->orderBy('codigo', 'asc')->get();
        }
        
        // Find parents for UI
        $recordsArray = $records->toArray();
        $parentCodes = [];
        for ($i = 0; $i < count($recordsArray) - 1; $i++) {
            $currentCode = (string)$recordsArray[$i]['codigo'];
            $nextCode = (string)$recordsArray[$i+1]['codigo'];
            if ($currentCode !== '' && str_starts_with($nextCode, $currentCode)) {
                $parentCodes[$currentCode] = true;
            }
        }
        
        $records->map(function($record) use ($parentCodes) {
            $record->is_parent = isset($parentCodes[(string)$record->codigo]);
            return $record;
        });
        
        return view('livewire.master-budget', [
            'records' => $records,
            'availableMonths' => range(1, 12),
        ])->layout('layouts.ledger');
    }
}
