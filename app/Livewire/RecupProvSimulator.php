<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ProvisionEngine;
use App\Models\FinancialHistory;
use App\Models\MasterBudgetRecord;
use App\Models\Projection;
use App\Models\ProjectionParameter;

class RecupProvSimulator extends Component
{
    // Parámetros editables por el usuario
    public $productiveRatio;
    public $consumerRatio;
    public $microcreditRatio;
    public $refinancedRatio;
    public $restructuredRatio;

    // Año de presupuesto
    public $budgetYear;

    // Ratios históricos calculados para comparación
    public $historicalRatios = [];
    public $historicalPortfolio = [];
    public $historicalProvisions = [];
    public $historicalPeriod = '';

    // Resultados de la simulación
    public $simulationResults = [];

    // Estado de integración
    public $isIntegrated = false;

    // Valores editables de recuperación de cartera (capital)
    public $recoveryValues = [];

    // Valores editables de recuperación de cartera (intereses)
    public $interestValues = [];

    public function mount()
    {
        $engine = new ProvisionEngine();
        $histData = $engine->calculateHistoricalData();

        // Inicializar año de presupuesto (2026 o posterior)
        $latestYear = FinancialHistory::max('año');
        $this->budgetYear = $latestYear ? $latestYear + 1 : (int)date('Y') + 1;

        // Cargar ratios históricos como valores iniciales
        $this->productiveRatio = round($histData['ratios']['productive'] * 100, 4);
        $this->consumerRatio = round($histData['ratios']['consumer'] * 100, 4);
        $this->microcreditRatio = round($histData['ratios']['microcredit'] * 100, 4);
        $this->refinancedRatio = round($histData['ratios']['refinanced'] * 100, 4);
        $this->restructuredRatio = round($histData['ratios']['restructured'] * 100, 4);

        $this->historicalRatios = $histData['ratios'];
        $this->historicalPortfolio = $histData['portfolio'];
        $this->historicalProvisions = $histData['provisions'];
        $this->historicalPeriod = $histData['año'] ? "{$histData['año']}-" . sprintf('%02d', $histData['mes']) : 'N/A';

        // Inicializar o cargar proyecciones de recuperación (capital)
        if (\App\Models\RecoveryProjection::where('tabla', 'capital')->count() === 0) {
            $this->seedRecoveryProjections();
        }
        $this->loadRecoveryValues();

        // Inicializar o cargar proyecciones de recuperación (intereses)
        if (\App\Models\RecoveryProjection::where('tabla', 'interes')->count() === 0) {
            $this->seedInterestProjections();
        }
        $this->loadInterestValues();

        // Los parámetros de cobertura se derivan de los totales de la relación porcentual de intereses
        $this->computeRatiosFromInterest();

        $this->calculate();
    }

    private function seedRecoveryProjections()
    {
        $defaults = [
            7 => ['concept' => 'Corporativo', 'months' => [1 => 0.0, 2 => 0.0, 3 => 0.0, 4 => 0.0, 5 => 0.0, 6 => 0.0, 7 => 0.0, 8 => 0.0, 9 => 0.0, 10 => 0.0, 11 => 0.0, 12 => 0.0]],
            8 => ['concept' => 'Empresarial', 'months' => [1 => 3028.63, 2 => 3057.96, 3 => 3206.29, 4 => 3118.64, 5 => 3186.44, 6 => 3179.71, 7 => 2246.12, 8 => 2241.98, 9 => 2273.39, 10 => 2337.68, 11 => 2337.46, 12 => 1400.27]],
            9 => ['concept' => 'Pymes', 'months' => [1 => 16444.73, 2 => 15059.03, 3 => 15059.03, 4 => 14541.35, 5 => 13344.00, 6 => 12816.04, 7 => 12112.32, 8 => 11595.81, 9 => 11236.96, 10 => 9523.47, 11 => 8524.52, 12 => 10804.35]],
            10 => ['concept' => 'CONSUMO', 'months' => [1 => 505492.50, 2 => 506987.68, 3 => 558097.25, 4 => 828439.46, 5 => 552397.35, 6 => 527156.02, 7 => 522183.85, 8 => 511744.04, 9 => 499898.72, 10 => 503054.57, 11 => 485042.47, 12 => 488189.98]],
            12 => ['concept' => 'Acumulación Ampliada', 'months' => [1 => 84736.10, 2 => 85424.21, 3 => 89385.05, 4 => 86175.83, 5 => 86471.82, 6 => 75441.38, 7 => 76443.25, 8 => 76266.44, 9 => 76291.04, 10 => 77512.69, 11 => 76771.85, 12 => 77588.75]],
            13 => ['concept' => 'Acumulación Simple', 'months' => [1 => 20095.16, 2 => 21984.53, 3 => 23418.46, 4 => 25333.42, 5 => 26497.48, 6 => 26059.87, 7 => 27062.82, 8 => 26652.65, 9 => 27029.52, 10 => 28157.81, 11 => 27802.68, 12 => 28917.01]],
            14 => ['concept' => 'Minorista', 'months' => [1 => 2470.17, 2 => 3903.97, 3 => 4862.38, 4 => 4485.01, 5 => 4699.03, 6 => 4621.15, 7 => 4832.76, 8 => 4761.41, 9 => 4831.98, 10 => 5039.90, 11 => 4978.54, 12 => 5183.82]],
            16 => ['concept' => 'Consumo', 'months' => [1 => 9513.61, 2 => 4388.33, 3 => 4654.77, 4 => 4101.39, 5 => 4229.79, 6 => 4213.32, 7 => 4339.58, 8 => 4328.30, 9 => 4386.46, 10 => 4509.35, 11 => 4284.09, 12 => 4401.81]],
            18 => ['concept' => 'Acumulación Ampliada', 'months' => [1 => 308.95, 2 => 313.25, 3 => 355.78, 4 => 322.59, 5 => 339.50, 6 => 331.82, 7 => 348.56, 8 => 341.31, 9 => 346.07, 10 => 362.54, 11 => 355.95, 12 => 372.25]],
            19 => ['concept' => 'Acumulación Simple', 'months' => [1 => 673.95, 2 => 951.92, 3 => 1375.90, 4 => 1260.26, 5 => 1321.03, 6 => 1295.40, 7 => 1355.51, 8 => 1331.49, 9 => 1349.63, 10 => 1408.72, 11 => 1387.19, 12 => 1445.58]],
            20 => ['concept' => 'Microcrédito Minorista', 'months' => [1 => 394.32, 2 => 1619.86, 3 => 2093.02, 4 => 2010.47, 5 => 2075.72, 6 => 2066.90, 7 => 2131.09, 8 => 2124.89, 9 => 2154.23, 10 => 2216.78, 11 => 2214.63, 12 => 2276.03]],
            22 => ['concept' => 'Consumo', 'months' => [1 => 7844.73, 2 => 8350.06, 3 => 9039.11, 4 => 8956.23, 5 => 9135.07, 6 => 8840.53, 7 => 9016.01, 8 => 9059.77, 9 => 8889.86, 10 => 9062.70, 11 => 8579.57, 12 => 8746.16]],
            24 => ['concept' => 'Acumulación Ampliada', 'months' => [1 => 1134.29, 2 => 1151.68, 3 => 1232.81, 4 => 1188.22, 5 => 1226.40, 6 => 1225.25, 7 => 1262.78, 8 => 1263.39, 9 => 1282.76, 10 => 1319.29, 11 => 1322.95, 12 => 1358.48]],
            25 => ['concept' => 'Acumulación Simple', 'months' => [1 => 906.34, 2 => 1052.05, 3 => 1229.74, 4 => 1156.58, 5 => 1213.94, 6 => 1192.97, 7 => 1249.69, 8 => 1230.45, 9 => 1249.32, 10 => 1305.08, 11 => 1288.54, 12 => 1343.60]],
            26 => ['concept' => 'Microcrédito Minorista', 'months' => [1 => 251.17, 2 => 254.99, 3 => 300.84, 4 => 263.48, 5 => 281.21, 6 => 271.79, 7 => 289.36, 8 => 280.35, 9 => 284.62, 10 => 301.97, 11 => 293.57, 12 => 310.77]]
        ];

        foreach ($defaults as $row => $data) {
            foreach ($data['months'] as $mes => $val) {
                \App\Models\RecoveryProjection::create([
                    'tabla' => 'capital',
                    'row_index' => $row,
                    'concept' => $data['concept'],
                    'mes' => $mes,
                    'valor' => $val
                ]);
            }
        }
    }

    private function seedInterestProjections()
    {
        $defaults = [
            33 => ['concept' => 'Corporativo', 'months' => [1 => 0.0, 2 => 0.0, 3 => 0.0, 4 => 0.0, 5 => 0.0, 6 => 0.0, 7 => 0.0, 8 => 0.0, 9 => 0.0, 10 => 0.0, 11 => 0.0, 12 => 0.0]],
            34 => ['concept' => 'Empresarial', 'months' => [1 => 1879.18, 2 => 2000.68, 3 => 1708.89, 4 => 1816.96, 5 => 1693.32, 6 => 1690.04, 7 => 1565.71, 8 => 1572.71, 9 => 1530.44, 10 => 1430.76, 11 => 1431.18, 12 => 1377.79]],
            35 => ['concept' => 'Pymes', 'months' => [1 => 4741.96, 2 => 5044.05, 3 => 4318.51, 4 => 4685.77, 5 => 4438.84, 6 => 4476.93, 7 => 4238.45, 8 => 4251.22, 9 => 4121.93, 10 => 3870.68, 11 => 3869.56, 12 => 3580.19]],
            36 => ['concept' => 'CONSUMO', 'months' => [1 => 187213.69, 2 => 227028.51, 3 => 180312.96, 4 => 220897.14, 5 => 184316.36, 6 => 185526.13, 7 => 175649.97, 8 => 177756.42, 9 => 172144.89, 10 => 163137.81, 11 => 164233.46, 12 => 156455.92]],
            38 => ['concept' => 'Acumulación Ampliada', 'months' => [1 => 46286.42, 2 => 45421.56, 3 => 40055.69, 4 => 42836.18, 5 => 40319.21, 6 => 40446.25, 7 => 38145.78, 8 => 38347.88, 9 => 37281.47, 10 => 35073.00, 11 => 35153.50, 12 => 33009.19]],
            39 => ['concept' => 'Acumulación Simple', 'months' => [1 => 23965.09, 2 => 30147.43, 3 => 23676.64, 4 => 25771.55, 5 => 24604.24, 6 => 25045.10, 7 => 23890.93, 8 => 24304.37, 9 => 23927.49, 10 => 22795.94, 11 => 23154.32, 12 => 22036.73]],
            40 => ['concept' => 'Minorista', 'months' => [1 => 5315.80, 2 => 5284.82, 3 => 4270.84, 4 => 4648.98, 5 => 4434.70, 6 => 4512.83, 7 => 4300.96, 8 => 4372.57, 9 => 4302.00, 10 => 4093.83, 11 => 4155.44, 12 => 3949.90]],
            42 => ['concept' => 'Consumo', 'months' => [1 => 2834.81, 2 => 2469.15, 3 => 2199.79, 4 => 2359.90, 5 => 2230.43, 6 => 2247.97, 7 => 2120.63, 8 => 2132.99, 9 => 2074.83, 10 => 1950.89, 11 => 1955.32, 12 => 1836.53]],
            44 => ['concept' => 'Acumulación Ampliada', 'months' => [1 => 408.82, 2 => 404.52, 3 => 361.43, 4 => 395.18, 5 => 378.09, 6 => 385.95, 7 => 369.02, 8 => 376.46, 9 => 371.70, 10 => 355.04, 11 => 361.82, 12 => 345.34]],
            45 => ['concept' => 'Acumulación Simple', 'months' => [1 => 1251.71, 2 => 1804.92, 3 => 1258.70, 4 => 1374.84, 5 => 1313.90, 6 => 1339.70, 7 => 1279.42, 8 => 1303.60, 9 => 1285.47, 10 => 1226.21, 11 => 1247.90, 12 => 1189.35]],
            46 => ['concept' => 'Microcrédito Minorista', 'months' => [1 => 1640.35, 2 => 1580.34, 3 => 1107.18, 4 => 1189.73, 5 => 1124.48, 6 => 1133.30, 7 => 1069.11, 8 => 1075.31, 9 => 1045.97, 10 => 983.42, 11 => 985.57, 12 => 924.17]],
            48 => ['concept' => 'Consumo', 'months' => [1 => 3184.08, 2 => 3915.07, 3 => 3225.18, 4 => 3308.90, 5 => 3129.91, 6 => 3069.13, 7 => 2893.37, 8 => 2849.67, 9 => 2712.54, 10 => 2539.12, 11 => 2474.73, 12 => 2307.86]],
            50 => ['concept' => 'Acumulación Ampliada', 'months' => [1 => 700.12, 2 => 682.72, 3 => 600.71, 4 => 646.18, 5 => 607.71, 6 => 609.15, 7 => 571.33, 8 => 571.01, 9 => 551.65, 10 => 514.82, 11 => 511.74, 12 => 475.61]],
            51 => ['concept' => 'Acumulación Simple', 'months' => [1 => 1200.08, 2 => 1350.22, 3 => 1172.53, 4 => 1245.69, 5 => 1188.33, 6 => 1209.30, 7 => 1152.58, 8 => 1171.82, 9 => 1152.95, 10 => 1097.19, 11 => 1113.73, 12 => 1058.67]],
            52 => ['concept' => 'Microcrédito Minorista', 'months' => [1 => 441.34, 2 => 437.52, 3 => 391.67, 4 => 429.03, 5 => 411.30, 6 => 420.72, 7 => 403.15, 8 => 412.16, 9 => 407.89, 10 => 390.54, 11 => 398.94, 12 => 381.74]]
        ];

        foreach ($defaults as $row => $data) {
            foreach ($data['months'] as $mes => $val) {
                \App\Models\RecoveryProjection::create([
                    'tabla' => 'interes',
                    'row_index' => $row,
                    'concept' => $data['concept'],
                    'mes' => $mes,
                    'valor' => $val
                ]);
            }
        }
    }

    private function loadRecoveryValues()
    {
        $records = \App\Models\RecoveryProjection::where('tabla', 'capital')->orderBy('row_index')->orderBy('mes')->get();
        $this->recoveryValues = [];
        foreach ($records as $r) {
            $this->recoveryValues[$r->row_index][$r->mes] = floatval($r->valor);
        }
    }

    private function loadInterestValues()
    {
        $records = \App\Models\RecoveryProjection::where('tabla', 'interes')->orderBy('row_index')->orderBy('mes')->get();
        $this->interestValues = [];
        foreach ($records as $r) {
            $this->interestValues[$r->row_index][$r->mes] = floatval($r->valor);
        }
    }

    public function getRecoveryProjectionsProperty()
    {
        $table = [];
        $leafIndices = [7, 8, 9, 10, 12, 13, 14, 16, 18, 19, 20, 22, 24, 25, 26];
        $concepts = [
            6 => 'PRODUCTIVO',
            7 => 'Corporativo',
            8 => 'Empresarial',
            9 => 'Pymes',
            10 => 'CONSUMO',
            11 => 'MICROCREDITO',
            12 => 'Acumulación Ampliada',
            13 => 'Acumulación Simple',
            14 => 'Minorista',
            15 => 'REFINANCIADA',
            16 => 'Consumo',
            17 => 'Microcrédito',
            18 => 'Acumulación Ampliada',
            19 => 'Acumulación Simple',
            20 => 'Microcrédito Minorista',
            21 => 'REESTRUCTURADA',
            22 => 'Consumo',
            23 => 'Microcrédito',
            24 => 'Acumulación Ampliada',
            25 => 'Acumulación Simple',
            26 => 'Microcrédito Minorista',
            27 => 'TOTAL'
        ];

        // Inicializar todas las filas
        for ($r = 6; $r <= 27; $r++) {
            $table[$r] = [
                'concept' => $concepts[$r],
                'is_parent' => in_array($r, [6, 11, 15, 17, 21, 23, 27]),
                'months' => array_fill(1, 12, 0.0),
                'total' => 0.0
            ];
        }

        // Cargar valores hojas
        foreach ($leafIndices as $r) {
            for ($m = 1; $m <= 12; $m++) {
                $val = floatval($this->recoveryValues[$r][$m] ?? 0.0);
                $table[$r]['months'][$m] = $val;
            }
        }

        // Calcular padres
        for ($m = 1; $m <= 12; $m++) {
            $table[17]['months'][$m] = $table[18]['months'][$m] + $table[19]['months'][$m] + $table[20]['months'][$m];
            $table[15]['months'][$m] = $table[16]['months'][$m] + $table[17]['months'][$m];
            $table[23]['months'][$m] = $table[24]['months'][$m] + $table[25]['months'][$m] + $table[26]['months'][$m];
            $table[21]['months'][$m] = $table[22]['months'][$m] + $table[23]['months'][$m];
            $table[6]['months'][$m] = $table[7]['months'][$m] + $table[8]['months'][$m] + $table[9]['months'][$m];
            $table[11]['months'][$m] = $table[12]['months'][$m] + $table[13]['months'][$m] + $table[14]['months'][$m];
            $table[27]['months'][$m] = $table[6]['months'][$m] + $table[10]['months'][$m] + $table[11]['months'][$m] + $table[15]['months'][$m] + $table[21]['months'][$m];
        }

        // Calcular totales anuales
        for ($r = 6; $r <= 27; $r++) {
            $table[$r]['total'] = array_sum($table[$r]['months']);
        }

        return $table;
    }

    public function saveRecovery()
    {
        $leafIndices = [7, 8, 9, 10, 12, 13, 14, 16, 18, 19, 20, 22, 24, 25, 26];
        foreach ($leafIndices as $r) {
            for ($m = 1; $m <= 12; $m++) {
                $val = floatval($this->recoveryValues[$r][$m] ?? 0.0);
                \App\Models\RecoveryProjection::updateOrCreate(
                    ['tabla' => 'capital', 'row_index' => $r, 'mes' => $m],
                    ['valor' => $val]
                );
            }
        }

        $table = $this->recoveryProjections;
        
        $totalProductive = $table[6]['total'];
        $totalConsumer = $table[10]['total'];
        $totalMicrocredit = $table[11]['total'];
        $totalRefinanced = $table[15]['total'];
        $totalRestructured = $table[21]['total'];

        $projection = Projection::latest()->first();
        if (!$projection) {
            $projection = Projection::create([
                'name' => 'Proyección Base 2026',
            ]);
        }

        $paramsData = [
            'recovery_productive' => $totalProductive,
            'recovery_consumer' => $totalConsumer,
            'recovery_microcredit' => $totalMicrocredit,
            'recovery_refinanced' => $totalRefinanced,
            'recovery_restructured' => $totalRestructured,
        ];

        if ($projection->parameters) {
            $projection->parameters->update($paramsData);
        } else {
            $projection->parameters()->create($paramsData);
        }

        session()->flash('success', 'La Proyección de Recuperación de Cartera ha sido guardada y sincronizada correctamente con los parámetros globales.');
    }

    public function getInterestProjectionsProperty()
    {
        $table = [];
        $leafIndices = [33, 34, 35, 36, 38, 39, 40, 42, 44, 45, 46, 48, 50, 51, 52];
        $concepts = [
            32 => 'PRODUCTIVO',
            33 => 'Corporativo',
            34 => 'Empresarial',
            35 => 'Pymes',
            36 => 'CONSUMO',
            37 => 'MICROCREDITO',
            38 => 'Acumulación Ampliada',
            39 => 'Acumulación Simple',
            40 => 'Minorista',
            41 => 'REFINANCIADA',
            42 => 'Consumo',
            43 => 'Microcrédito',
            44 => 'Acumulación Ampliada',
            45 => 'Acumulación Simple',
            46 => 'Microcrédito Minorista',
            47 => 'REESTRUCTURADA',
            48 => 'Consumo',
            49 => 'Microcrédito',
            50 => 'Acumulación Ampliada',
            51 => 'Acumulación Simple',
            52 => 'Microcrédito Minorista',
            53 => 'TOTAL'
        ];

        // Inicializar todas las filas
        for ($r = 32; $r <= 53; $r++) {
            $table[$r] = [
                'concept' => $concepts[$r],
                'is_parent' => in_array($r, [32, 37, 41, 43, 47, 49, 53]),
                'months' => array_fill(1, 12, 0.0),
                'total' => 0.0
            ];
        }

        // Cargar valores hojas
        foreach ($leafIndices as $r) {
            for ($m = 1; $m <= 12; $m++) {
                $table[$r]['months'][$m] = floatval($this->interestValues[$r][$m] ?? 0.0);
            }
        }

        // Calcular padres (misma jerarquía que capital desplazada +26)
        for ($m = 1; $m <= 12; $m++) {
            $table[43]['months'][$m] = $table[44]['months'][$m] + $table[45]['months'][$m] + $table[46]['months'][$m];
            $table[41]['months'][$m] = $table[42]['months'][$m] + $table[43]['months'][$m];
            $table[49]['months'][$m] = $table[50]['months'][$m] + $table[51]['months'][$m] + $table[52]['months'][$m];
            $table[47]['months'][$m] = $table[48]['months'][$m] + $table[49]['months'][$m];
            $table[32]['months'][$m] = $table[33]['months'][$m] + $table[34]['months'][$m] + $table[35]['months'][$m];
            $table[37]['months'][$m] = $table[38]['months'][$m] + $table[39]['months'][$m] + $table[40]['months'][$m];
            $table[53]['months'][$m] = $table[32]['months'][$m] + $table[36]['months'][$m] + $table[37]['months'][$m] + $table[41]['months'][$m] + $table[47]['months'][$m];
        }

        // Calcular totales anuales
        for ($r = 32; $r <= 53; $r++) {
            $table[$r]['total'] = array_sum($table[$r]['months']);
        }

        return $table;
    }

    public function saveInterest()
    {
        $leafIndices = [33, 34, 35, 36, 38, 39, 40, 42, 44, 45, 46, 48, 50, 51, 52];
        foreach ($leafIndices as $r) {
            for ($m = 1; $m <= 12; $m++) {
                $val = floatval($this->interestValues[$r][$m] ?? 0.0);
                \App\Models\RecoveryProjection::updateOrCreate(
                    ['tabla' => 'interes', 'row_index' => $r, 'mes' => $m],
                    ['valor' => $val]
                );
            }
        }

        session()->flash('success', 'La Proyección de Recuperación de Cartera (Intereses) ha sido guardada correctamente. Los Parámetros de Cobertura han sido actualizados.');

        // Actualizar los parámetros de cobertura con los nuevos porcentajes
        $this->computeRatiosFromInterest();
        $this->calculate();
    }

    /**
     * Calcula los ratios de cobertura a partir de los totales porcentuales
     * de la tabla de relación porcentual de recuperación de intereses.
     * PRODUCTIVO = row 32, CONSUMO = row 36, MICROCREDITO = row 37,
     * REFINANCIADA = row 41, REESTRUCTURADA = row 47, TOTAL = row 53
     */
    private function computeRatiosFromInterest(): void
    {
        $ip = $this->interestProjections;
        $totalAnual = $ip[53]['total'] ?? 0;

        if ($totalAnual > 0) {
            $this->productiveRatio   = round(($ip[32]['total'] / $totalAnual) * 100, 4);
            $this->consumerRatio     = round(($ip[36]['total'] / $totalAnual) * 100, 4);
            $this->microcreditRatio  = round(($ip[37]['total'] / $totalAnual) * 100, 4);
            $this->refinancedRatio   = round(($ip[41]['total'] / $totalAnual) * 100, 4);
            $this->restructuredRatio = round(($ip[47]['total'] / $totalAnual) * 100, 4);
        }
    }


    /**
     * Ejecuta la simulación con los ratios provistos.
     */
    public function calculate()
    {
        // Validar entradas
        $this->validate([
            'productiveRatio' => 'required|numeric|min:0|max:100',
            'consumerRatio' => 'required|numeric|min:0|max:100',
            'microcreditRatio' => 'required|numeric|min:0|max:100',
            'refinancedRatio' => 'required|numeric|min:0|max:100',
            'restructuredRatio' => 'required|numeric|min:0|max:100',
        ]);

        $engine = new ProvisionEngine();

        // Convertir de porcentajes a decimales
        $ratios = [
            'productive' => $this->productiveRatio / 100,
            'consumer' => $this->consumerRatio / 100,
            'microcredit' => $this->microcreditRatio / 100,
            'refinanced' => $this->refinancedRatio / 100,
            'restructured' => $this->restructuredRatio / 100,
        ];

        $this->simulationResults = $engine->simulate($ratios, $this->budgetYear);
    }

    /**
     * Guarda la proyección en la tabla master_budget_records.
     */
    public function integrate()
    {
        $this->calculate();

        $engine = new ProvisionEngine();
        $engine->integrateIntoBudget($this->simulationResults, $this->budgetYear);

        $this->isIntegrated = true;

        session()->flash('success', 'Las provisiones y gastos proyectados se integraron correctamente al Presupuesto Maestro 2026.');
    }

    public function restoreHistorical()
    {
        $engine = new ProvisionEngine();
        $histData = $engine->calculateHistoricalData();

        $this->productiveRatio = round($histData['ratios']['productive'] * 100, 4);
        $this->consumerRatio = round($histData['ratios']['consumer'] * 100, 4);
        $this->microcreditRatio = round($histData['ratios']['microcredit'] * 100, 4);
        $this->refinancedRatio = round($histData['ratios']['refinanced'] * 100, 4);
        $this->restructuredRatio = round($histData['ratios']['restructured'] * 100, 4);

        $this->calculate();
        
        session()->flash('success', 'Ratios de cobertura restaurados al baseline histórico.');
    }

    public function render()
    {
        return view('livewire.recup-prov-simulator')
            ->layout('layouts.ledger');
    }
}
