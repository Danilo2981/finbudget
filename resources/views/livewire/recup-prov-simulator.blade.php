<div class="space-y-8">
    <!-- Header Page -->
    <div class="flex justify-between items-center bg-white dark:bg-[#0b132b] p-6 rounded-2xl shadow-sm border border-slate-200/40">
        <div>
            <h2 class="text-2xl font-extrabold text-on-surface tracking-tight">Simulador de Recuperaciones y Provisiones</h2>
            <p class="text-slate-500 text-sm mt-1">Simula las provisiones de cartera de crédito y calcula el gasto mensual para integrarlo al Presupuesto Maestro {{ $budgetYear }}.</p>
        </div>
        <div>
            <button 
                wire:click="integrate" 
                wire:loading.attr="disabled"
                class="flex items-center gap-2 bg-[#001736] hover:bg-[#002b5b] text-[#85f8c4] font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200"
            >
                <span wire:loading class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full" role="status"></span>
                <span wire:loading.remove class="material-symbols-outlined text-lg">check_circle</span>
                <span>Integrar al Presupuesto Maestro</span>
            </button>
        </div>
    </div>

    <!-- Alert Success -->
    @if (session()->has('success'))
        <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-500/30 text-emerald-800 dark:text-emerald-300 p-4 rounded-xl flex items-center gap-3 shadow-sm">
            <span class="material-symbols-outlined text-xl">verified</span>
            <div class="text-sm font-semibold">{{ session('success') }}</div>
        </div>
    @endif

    <!-- Main Grid: Parameters & Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Parameter Panel -->
        <div class="lg:col-span-1 bg-white dark:bg-[#0b132b] p-6 rounded-2xl shadow-sm border border-slate-200/40 space-y-5">
            <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary dark:text-[#85f8c4]">tune</span>
                Parámetros de Cobertura
            </h3>
            <div class="bg-blue-50/60 dark:bg-blue-950/20 border border-blue-200/40 rounded-xl p-3 text-[10px] text-blue-800 dark:text-blue-300 flex gap-2 items-start">
                <span class="material-symbols-outlined text-sm shrink-0 mt-0.5">info</span>
                <span>Valores derivados automáticamente de la <strong>Relación Porcentual de Recuperación de Intereses</strong> (columna TOTAL). Actualiza la tabla de intereses y presiona <em>Guardar Proyección</em> para sincronizar.</span>
            </div>

            <hr class="border-slate-100 dark:border-slate-800">

            @php
                $ip = $this->interestProjections;
                $totalAnual = $ip[53]['total'] ?? 0;
                $segments = [
                    ['label' => 'Crédito Productivo',   'row' => 32, 'color' => 'text-blue-600 dark:text-blue-400',   'bg' => 'bg-blue-50 dark:bg-blue-950/30',   'bar' => 'bg-blue-500',   'ratio' => $productiveRatio],
                    ['label' => 'Crédito de Consumo',   'row' => 36, 'color' => 'text-emerald-600 dark:text-emerald-400','bg' => 'bg-emerald-50 dark:bg-emerald-950/30','bar' => 'bg-emerald-500','ratio' => $consumerRatio],
                    ['label' => 'Microcrédito',          'row' => 37, 'color' => 'text-amber-600 dark:text-amber-400',  'bg' => 'bg-amber-50 dark:bg-amber-950/30',   'bar' => 'bg-amber-500',   'ratio' => $microcreditRatio],
                    ['label' => 'C. Refinanciada',       'row' => 41, 'color' => 'text-violet-600 dark:text-violet-400','bg' => 'bg-violet-50 dark:bg-violet-950/30', 'bar' => 'bg-violet-500', 'ratio' => $refinancedRatio],
                    ['label' => 'C. Reestructurada',     'row' => 47, 'color' => 'text-rose-600 dark:text-rose-400',    'bg' => 'bg-rose-50 dark:bg-rose-950/30',     'bar' => 'bg-rose-500',    'ratio' => $restructuredRatio],
                ];
            @endphp

            @foreach ($segments as $seg)
                @php
                    $pct = $seg['ratio'];
                    $barWidth = min(100, max(0, $pct));
                @endphp
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs font-semibold">
                        <span class="text-slate-600 dark:text-slate-300">{{ $seg['label'] }}</span>
                        <span class="{{ $seg['color'] }} font-bold tabular-nums">{{ number_format($pct, 2) }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                        <div class="{{ $seg['bar'] }} h-2 rounded-full transition-all duration-500" style="width: {{ $barWidth }}%"></div>
                    </div>
                    <div class="text-[10px] text-slate-400 flex justify-between">
                        <span>Aporte al total de intereses</span>
                        <span>${{ number_format($ip[$seg['row']]['total'] ?? 0, 2) }}</span>
                    </div>
                </div>
            @endforeach

            <!-- Total check -->
            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 flex justify-between items-center border border-slate-200/40 dark:border-slate-800/40">
                <span class="text-xs font-bold text-on-surface">TOTAL</span>
                <span class="text-xs font-extrabold text-primary dark:text-[#85f8c4] tabular-nums">
                    {{ number_format($productiveRatio + $consumerRatio + $microcreditRatio + $refinancedRatio + $restructuredRatio, 2) }}%
                </span>
            </div>

            <!-- Total Intereses -->
            <div class="text-[10px] text-slate-400 text-center">
                Total intereses proyectados: <strong class="text-slate-600 dark:text-slate-300">${{ number_format($totalAnual, 2) }}</strong>
            </div>
        </div>

        <!-- Simulation Grid / Visuals -->
        <div class="lg:col-span-3 bg-white dark:bg-[#0b132b] p-6 rounded-2xl shadow-sm border border-slate-200/40 space-y-6 flex flex-col justify-between">
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary dark:text-[#85f8c4]">insights</span>
                    Resumen de Impacto Anual Proyectado (2026)
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Card 1: Cartera Total Promedio -->
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                        <div class="text-xs text-slate-400 font-semibold uppercase">Cartera Total Diciembre 2026</div>
                        <div class="text-xl font-bold text-on-surface tabular-nums mt-1">
                            ${{ number_format(array_sum($simulationResults[12]['portfolio'] ?? []), 2) }}
                        </div>
                    </div>

                    <!-- Card 2: Provisión Acumulada Diciembre -->
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                        <div class="text-xs text-slate-400 font-semibold uppercase">Provisión Acumulada Requerida (Dic)</div>
                        <div class="text-xl font-bold text-rose-600 dark:text-rose-400 tabular-nums mt-1">
                            -${{ number_format(array_sum(array_slice($simulationResults[12]['provisions_acum'] ?? [], 0, 5)), 2) }}
                        </div>
                    </div>

                    <!-- Card 3: Gasto Total de Provisión Anual -->
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                        <div class="text-xs text-slate-400 font-semibold uppercase">Gasto Neto de Provisión Anual (2026)</div>
                        @php
                            $totalGasto = 0;
                            foreach ($simulationResults as $res) {
                                $totalGasto += array_sum($res['provision_gasto']);
                            }
                        @endphp
                        <div class="text-xl font-bold {{ $totalGasto >= 0 ? 'text-rose-600' : 'text-emerald-600' }} tabular-nums mt-1">
                            ${{ number_format($totalGasto, 2) }}
                        </div>
                    </div>
                </div>
                
                <!-- Chart Wrapper -->
                <div 
                    wire:ignore
                    x-data="{
                        results: $wire.entangle('simulationResults'),
                        initChart() {
                            const existingChart = Chart.getChart('provChart');
                            if (existingChart) existingChart.destroy();
                            const ctx = document.getElementById('provChart').getContext('2d');
                            
                            const data = this.results;
                            const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            
                            const provisions = months.map((m, idx) => {
                                const monthData = data[idx + 1] || {};
                                const acum = monthData.provisions_acum || {};
                                return (parseFloat(acum.productive) || 0) + (parseFloat(acum.consumer) || 0) + (parseFloat(acum.microcredit) || 0) + (parseFloat(acum.refinanced) || 0) + (parseFloat(acum.restructured) || 0);
                            });
                            
                            const expenses = months.map((m, idx) => {
                                const monthData = data[idx + 1] || {};
                                const gast = monthData.provision_gasto || {};
                                return (parseFloat(gast.productive) || 0) + (parseFloat(gast.consumer) || 0) + (parseFloat(gast.microcredit) || 0) + (parseFloat(gast.refinanced) || 0) + (parseFloat(gast.restructured) || 0);
                            });

                            this.chart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: months,
                                    datasets: [
                                        {
                                            label: 'Gasto Mensual ($)',
                                            data: expenses,
                                            backgroundColor: expenses.map(v => v >= 0 ? 'rgba(239, 68, 68, 0.7)' : 'rgba(16, 185, 129, 0.7)'),
                                            borderColor: expenses.map(v => v >= 0 ? '#ef4444' : '#10b981'),
                                            borderWidth: 1.5,
                                            borderRadius: 4,
                                            order: 2,
                                            yAxisID: 'yGasto'
                                        },
                                        {
                                            label: 'Provisión Acumulada ($)',
                                            data: provisions,
                                            type: 'line',
                                            borderColor: '#405f91',
                                            backgroundColor: 'rgba(64, 95, 145, 0.08)',
                                            fill: true,
                                            tension: 0.35,
                                            borderWidth: 2,
                                            pointBackgroundColor: '#405f91',
                                            pointRadius: 3,
                                            order: 1,
                                            yAxisID: 'yAcum'
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'top',
                                            labels: {
                                                font: { family: 'Inter', size: 10, weight: '500' },
                                                boxWidth: 12,
                                                padding: 15,
                                                color: '#475569'
                                            }
                                        },
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false,
                                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                            titleFont: { family: 'Inter', size: 11, weight: 'bold' },
                                            bodyFont: { family: 'Inter', size: 11 },
                                            padding: 10,
                                            cornerRadius: 8,
                                            callbacks: {
                                                label: function(context) {
                                                    let label = context.dataset.label.split(' ')[0] || '';
                                                    if (label) label += ': ';
                                                    if (context.raw !== null) {
                                                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.raw);
                                                    }
                                                    return label;
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        x: {
                                            grid: { display: false },
                                            ticks: {
                                                font: { family: 'Inter', size: 10 },
                                                color: '#64748b'
                                            }
                                        },
                                        yAcum: {
                                            type: 'linear',
                                            position: 'left',
                                            ticks: {
                                                font: { family: 'Inter', size: 9 },
                                                color: '#405f91',
                                                callback: function(value) {
                                                    return '$' + new Intl.NumberFormat('en-US', { notation: 'compact' }).format(value);
                                                }
                                            },
                                            grid: {
                                                color: 'rgba(148, 163, 184, 0.1)'
                                            }
                                        },
                                        yGasto: {
                                            type: 'linear',
                                            position: 'right',
                                            ticks: {
                                                font: { family: 'Inter', size: 9 },
                                                color: '#64748b',
                                                callback: function(value) {
                                                    return '$' + new Intl.NumberFormat('en-US', { notation: 'compact' }).format(value);
                                                }
                                            },
                                            grid: { display: false }
                                        }
                                    }
                                }
                            });
                        }
                    }"
                    x-init="
                        if (!window.Chart) {
                            let script = document.createElement('script');
                            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                            script.onload = () => { initChart(); };
                            document.head.appendChild(script);
                        } else {
                            initChart();
                        }
                        
                        $watch('results', () => {
                            initChart();
                        });
                    "
                    class="relative w-full h-56 mt-4"
                >
                    <canvas id="provChart"></canvas>
                </div>

                <!-- Portfolio Chart Wrapper -->
                <div 
                    wire:ignore
                    x-data="{
                        results: $wire.entangle('simulationResults'),
                        initChart() {
                            const existingChart = Chart.getChart('portfolioChart');
                            if (existingChart) existingChart.destroy();
                            const ctx = document.getElementById('portfolioChart').getContext('2d');
                            
                            const data = this.results;
                            const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            
                            const productive = months.map((m, idx) => (parseFloat(data[idx + 1]?.portfolio?.productive) || 0));
                            const consumer = months.map((m, idx) => (parseFloat(data[idx + 1]?.portfolio?.consumer) || 0));
                            const microcredit = months.map((m, idx) => (parseFloat(data[idx + 1]?.portfolio?.microcredit) || 0));
                            const refinanced = months.map((m, idx) => (parseFloat(data[idx + 1]?.portfolio?.refinanced) || 0));
                            const restructured = months.map((m, idx) => (parseFloat(data[idx + 1]?.portfolio?.restructured) || 0));

                            this.chart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: months,
                                    datasets: [
                                        {
                                            label: 'Productivo',
                                            data: productive,
                                            backgroundColor: '#3b82f6',
                                        },
                                        {
                                            label: 'Consumo',
                                            data: consumer,
                                            backgroundColor: '#10b981',
                                        },
                                        {
                                            label: 'Microcrédito',
                                            data: microcredit,
                                            backgroundColor: '#f59e0b',
                                        },
                                        {
                                            label: 'Refinanciada',
                                            data: refinanced,
                                            backgroundColor: '#8b5cf6',
                                        },
                                        {
                                            label: 'Reestructurada',
                                            data: restructured,
                                            backgroundColor: '#ec4899',
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'top',
                                            labels: {
                                                font: { family: 'Inter', size: 10, weight: '500' },
                                                boxWidth: 12,
                                                color: '#475569'
                                            }
                                        },
                                        title: {
                                            display: true,
                                            text: 'Distribución de Cartera Proyectada por Segmento ($)',
                                            font: { family: 'Inter', size: 11, weight: 'bold' },
                                            color: '#1e293b',
                                            padding: { bottom: 10 }
                                        },
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false,
                                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                            titleFont: { family: 'Inter', size: 11, weight: 'bold' },
                                            bodyFont: { family: 'Inter', size: 11 },
                                            padding: 10,
                                            cornerRadius: 8,
                                            callbacks: {
                                                label: function(context) {
                                                    let label = context.dataset.label || '';
                                                    if (label) label += ': ';
                                                    if (context.raw !== null) {
                                                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.raw);
                                                    }
                                                    return label;
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        x: {
                                            stacked: true,
                                            grid: { display: false },
                                            ticks: {
                                                font: { family: 'Inter', size: 10 },
                                                color: '#64748b'
                                            }
                                        },
                                        y: {
                                            stacked: true,
                                            ticks: {
                                                font: { family: 'Inter', size: 9 },
                                                color: '#64748b',
                                                callback: function(value) {
                                                    return '$' + new Intl.NumberFormat('en-US', { notation: 'compact' }).format(value);
                                                }
                                            },
                                            grid: {
                                                color: 'rgba(148, 163, 184, 0.1)'
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    }"
                    x-init="
                        if (!window.Chart) {
                            let script = document.createElement('script');
                            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                            script.onload = () => { initChart(); };
                            document.head.appendChild(script);
                        } else {
                            initChart();
                        }
                        
                        $watch('results', () => {
                            initChart();
                        });
                    "
                    class="relative w-full h-56 mt-6 pt-4 border-t border-slate-100 dark:border-slate-800"
                >
                    <canvas id="portfolioChart"></canvas>
                </div>
            </div>

            <!-- Historical Info Box -->
            <div class="bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/40 p-4 rounded-xl text-xs text-blue-900 dark:text-blue-300 space-y-2 mt-4">
                <div class="font-bold flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">info</span>
                    Relación con el Histórico Contable
                </div>
                <p>Las provisiones históricas disminuyen directamente la valoración total del activo. Los gastos se envían a la sección de Pérdidas y Ganancias. La simulación respeta los cierres reales de la institución y permite afinar los ratios para cumplir metas regulatorias o de gestión.</p>
            </div>
        </div>
    </div>


    <!-- Recovery Projection Interactive Table -->
    <div class="bg-white dark:bg-[#0b132b] p-6 rounded-2xl shadow-sm border border-slate-200/40 space-y-4">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary dark:text-[#85f8c4]">payments</span>
                    PROYECCIÓN RECUPERACIÓN CARTERA DE CRÉDITO (CAPITAL)
                </h3>
                <p class="text-slate-400 text-xs mt-1">Digita las proyecciones de recuperación mensual para cada segmento. Las sumas se recalculan en tiempo real.</p>
            </div>
            <div>
                <button 
                    wire:click="saveRecovery" 
                    wire:loading.attr="disabled"
                    class="flex items-center gap-2 bg-[#001736] hover:bg-[#002b5b] text-[#85f8c4] font-semibold px-4 py-2 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 text-xs"
                >
                    <span wire:loading wire:target="saveRecovery" class="animate-spin inline-block w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full" role="status"></span>
                    <span wire:loading.remove wire:target="saveRecovery" class="material-symbols-outlined text-sm">save</span>
                    <span>Guardar Proyección</span>
                </button>
            </div>
        </div>

        <!-- Horizontal Scrollable Container -->
        <div class="overflow-x-auto rounded-xl border border-slate-200/30">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900 text-slate-400 border-b border-slate-200/30">
                        <th class="p-3 font-semibold">SEGMENTO CREDITO</th>
                        <th class="p-3 font-semibold text-right">Dic (Base)</th>
                        @for ($m = 1; $m <= 12; $m++)
                            <th class="p-3 font-semibold text-right">{{ date('M', mktime(0, 0, 0, $m, 1)) }}</th>
                        @endfor
                        <th class="p-3 font-semibold text-right">TOTALES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-on-surface">
                    @foreach ($this->recoveryProjections as $rowIndex => $row)
                        @php
                            $isParent = $row['is_parent'];
                            
                            // Determinar nivel de indentación
                            $indentClass = 'pl-3';
                            if (in_array($rowIndex, [7, 8, 9, 12, 13, 14, 16, 22])) {
                                $indentClass = 'pl-8';
                            } elseif (in_array($rowIndex, [17, 23])) {
                                $indentClass = 'pl-6';
                            } elseif (in_array($rowIndex, [18, 19, 20, 24, 25, 26])) {
                                $indentClass = 'pl-12';
                            }

                            // Clases de fila según importancia
                            $rowClass = 'hover:bg-slate-50/30 dark:hover:bg-slate-800/10';
                            if ($rowIndex === 27) {
                                $rowClass = 'bg-slate-100/80 dark:bg-slate-900 font-extrabold text-primary dark:text-[#85f8c4] border-t-2 border-slate-300 dark:border-slate-700';
                            } elseif ($isParent) {
                                $rowClass = 'bg-slate-50 dark:bg-slate-900/50 font-bold text-slate-800 dark:text-[#85f8c4]';
                            }
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <!-- Col A: Segmento -->
                            <td class="p-2 {{ $indentClass }} font-medium">
                                {{ $row['concept'] }}
                            </td>
                            
                            <!-- Col B: Dic Base -->
                            <td class="p-2 text-right tabular-nums text-slate-400">
                                $0.00
                            </td>

                            <!-- Cols C-N: Ene-Dic -->
                            @for ($m = 1; $m <= 12; $m++)
                                <td class="p-1 text-right">
                                    @if ($isParent)
                                        <span class="tabular-nums font-semibold pr-2">
                                            ${{ number_format($row['months'][$m], 2) }}
                                        </span>
                                    @else
                                        <div class="relative flex items-center justify-end">
                                            <span class="absolute left-1 text-slate-400 pointer-events-none select-none text-[10px]">$</span>
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                wire:model.live="recoveryValues.{{ $rowIndex }}.{{ $m }}" 
                                                class="w-24 bg-transparent text-right border border-transparent hover:border-slate-200 dark:hover:border-slate-800 focus:border-primary dark:focus:border-[#85f8c4] focus:ring-0 focus:outline-none rounded px-1 py-0.5 text-xs tabular-nums text-slate-700 dark:text-slate-200" 
                                            />
                                        </div>
                                    @endif
                                </td>
                            @endfor

                            <!-- Col O: Total -->
                            <td class="p-2 text-right tabular-nums font-semibold">
                                ${{ number_format($row['total'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Interest Recovery Projection Interactive Table -->
    <div class="bg-white dark:bg-[#0b132b] p-6 rounded-2xl shadow-sm border border-slate-200/40 space-y-4">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary dark:text-[#85f8c4]">account_balance</span>
                    PROYECCIÓN RECUPERACIÓN CARTERA DE CRÉDITO (INTERESES)
                </h3>
                <p class="text-slate-400 text-xs mt-1">Digita las proyecciones de recuperación de intereses mensual para cada segmento. Las sumas se recalculan en tiempo real.</p>
            </div>
            <div>
                <button 
                    wire:click="saveInterest" 
                    wire:loading.attr="disabled"
                    class="flex items-center gap-2 bg-[#001736] hover:bg-[#002b5b] text-[#85f8c4] font-semibold px-4 py-2 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 text-xs"
                >
                    <span wire:loading wire:target="saveInterest" class="animate-spin inline-block w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full" role="status"></span>
                    <span wire:loading.remove wire:target="saveInterest" class="material-symbols-outlined text-sm">save</span>
                    <span>Guardar Proyección</span>
                </button>
            </div>
        </div>

        <!-- Horizontal Scrollable Container -->
        <div class="overflow-x-auto rounded-xl border border-slate-200/30">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900 text-slate-400 border-b border-slate-200/30">
                        <th class="p-3 font-semibold">SEGMENTO CREDITO</th>
                        <th class="p-3 font-semibold text-right">Dic (Base)</th>
                        @for ($m = 1; $m <= 12; $m++)
                            <th class="p-3 font-semibold text-right">{{ date('M', mktime(0, 0, 0, $m, 1)) }}</th>
                        @endfor
                        <th class="p-3 font-semibold text-right">TOTALES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-on-surface">
                    @foreach ($this->interestProjections as $rowIndex => $row)
                        @php
                            $isParent = $row['is_parent'];

                            // Determinar nivel de indentación (misma lógica que tabla de capital)
                            $indentClass = 'pl-3';
                            if (in_array($rowIndex, [33, 34, 35, 38, 39, 40, 42, 48])) {
                                $indentClass = 'pl-8';
                            } elseif (in_array($rowIndex, [43, 49])) {
                                $indentClass = 'pl-6';
                            } elseif (in_array($rowIndex, [44, 45, 46, 50, 51, 52])) {
                                $indentClass = 'pl-12';
                            }

                            $rowClass = 'hover:bg-slate-50/30 dark:hover:bg-slate-800/10';
                            if ($rowIndex === 53) {
                                $rowClass = 'bg-slate-100/80 dark:bg-slate-900 font-extrabold text-primary dark:text-[#85f8c4] border-t-2 border-slate-300 dark:border-slate-700';
                            } elseif ($isParent) {
                                $rowClass = 'bg-slate-50 dark:bg-slate-900/50 font-bold text-slate-800 dark:text-[#85f8c4]';
                            }
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <!-- Col A: Segmento -->
                            <td class="p-2 {{ $indentClass }} font-medium">
                                {{ $row['concept'] }}
                            </td>

                            <!-- Col B: Dic Base -->
                            <td class="p-2 text-right tabular-nums text-slate-400">
                                $0.00
                            </td>

                            <!-- Cols C-N: Ene-Dic -->
                            @for ($m = 1; $m <= 12; $m++)
                                <td class="p-1 text-right">
                                    @if ($isParent)
                                        <span class="tabular-nums font-semibold pr-2">
                                            ${{ number_format($row['months'][$m], 2) }}
                                        </span>
                                    @else
                                        <div class="relative flex items-center justify-end">
                                            <span class="absolute left-1 text-slate-400 pointer-events-none select-none text-[10px]">$</span>
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                wire:model.live="interestValues.{{ $rowIndex }}.{{ $m }}" 
                                                class="w-24 bg-transparent text-right border border-transparent hover:border-slate-200 dark:hover:border-slate-800 focus:border-primary dark:focus:border-[#85f8c4] focus:ring-0 focus:outline-none rounded px-1 py-0.5 text-xs tabular-nums text-slate-700 dark:text-slate-200" 
                                            />
                                        </div>
                                    @endif
                                </td>
                            @endfor

                            <!-- Col O: Total -->
                            <td class="p-2 text-right tabular-nums font-semibold">
                                ${{ number_format($row['total'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Relación Porcentual de la Proyección por Recuperación de Intereses -->
    <div class="bg-white dark:bg-[#0b132b] p-6 rounded-2xl shadow-sm border border-slate-200/40 space-y-4">
        <div>
            <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary dark:text-[#85f8c4]">percent</span>
                RELACIÓN PORCENTUAL DE LA PROYECCIÓN POR RECUPERACIÓN DE INTERESES - APORTE POR PRODUCTO
            </h3>
            <p class="text-slate-400 text-xs mt-1">Distribución porcentual del aporte de cada segmento al total de recuperación de intereses. Se recalcula automáticamente al actualizar la tabla de intereses.</p>
        </div>

        <!-- Horizontal Scrollable Container -->
        <div class="overflow-x-auto rounded-xl border border-slate-200/30">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900 text-slate-400 border-b border-slate-200/30">
                        <th class="p-3 font-semibold">SEGMENTO CREDITO</th>
                        <th class="p-3 font-semibold text-right">Dic (Base)</th>
                        @for ($m = 1; $m <= 12; $m++)
                            <th class="p-3 font-semibold text-right">{{ date('M', mktime(0, 0, 0, $m, 1)) }}</th>
                        @endfor
                        <th class="p-3 font-semibold text-right">TOTAL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-on-surface">
                    @php
                        $ip = $this->interestProjections;
                        $pctRows = [
                            32 => ['label' => 'PRODUCTIVO',    'color' => 'text-blue-600 dark:text-blue-400'],
                            36 => ['label' => 'CONSUMO',       'color' => 'text-emerald-600 dark:text-emerald-400'],
                            37 => ['label' => 'MICROCREDITO',  'color' => 'text-amber-600 dark:text-amber-400'],
                            41 => ['label' => 'REFINANCIADA',  'color' => 'text-violet-600 dark:text-violet-400'],
                            47 => ['label' => 'REESTRUCTURADA','color' => 'text-rose-600 dark:text-rose-400'],
                        ];
                        $totalRow = $ip[53] ?? null;
                    @endphp

                    @foreach ($pctRows as $rowIdx => $meta)
                        @php
                            $segRow = $ip[$rowIdx] ?? null;
                        @endphp
                        <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/10">
                            <td class="p-3 font-semibold {{ $meta['color'] }}">{{ $meta['label'] }}</td>
                            
                            <!-- Dic Base -->
                            <td class="p-3 text-right tabular-nums text-slate-400">—</td>

                            @for ($m = 1; $m <= 12; $m++)
                                @php
                                    $seg = $segRow ? ($segRow['months'][$m] ?? 0) : 0;
                                    $tot = $totalRow ? ($totalRow['months'][$m] ?? 0) : 0;
                                    $pct = $tot > 0 ? ($seg / $tot * 100) : 0;
                                @endphp
                                <td class="p-3 text-right tabular-nums {{ $meta['color'] }}">
                                    {{ number_format($pct, 2) }}%
                                </td>
                            @endfor

                            @php
                                $segTotal = $segRow ? ($segRow['total'] ?? 0) : 0;
                                $totTotal = $totalRow ? ($totalRow['total'] ?? 0) : 0;
                                $pctTotal = $totTotal > 0 ? ($segTotal / $totTotal * 100) : 0;
                            @endphp
                            <td class="p-3 text-right tabular-nums font-bold {{ $meta['color'] }}">
                                {{ number_format($pctTotal, 2) }}%
                            </td>
                        </tr>
                    @endforeach

                    <!-- TOTAL row: always 100% -->
                    <tr class="bg-slate-100/80 dark:bg-slate-900 font-extrabold text-primary dark:text-[#85f8c4] border-t-2 border-slate-300 dark:border-slate-700">
                        <td class="p-3">TOTAL</td>
                        <td class="p-3 text-right tabular-nums text-slate-400">—</td>
                        @for ($m = 1; $m <= 12; $m++)
                            @php
                                $tot = $totalRow ? ($totalRow['months'][$m] ?? 0) : 0;
                            @endphp
                            <td class="p-3 text-right tabular-nums">
                                {{ $tot > 0 ? '100.00%' : '—' }}
                            </td>
                        @endfor
                        <td class="p-3 text-right tabular-nums">
                            {{ ($totalRow && $totalRow['total'] > 0) ? '100.00%' : '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
