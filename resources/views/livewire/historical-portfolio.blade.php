<div class="max-w-7xl mx-auto" x-data="{
    chart: null,
    results: @entangle('trend'),
    initChart() {
        const ctx = document.getElementById('portfolioHistoryChart');
        if (!ctx) return;
        
        // Clean up previous instance
        if (this.chart) {
            this.chart.destroy();
        }
        
        const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        // Segments to display (excluding provisiones which is negative)
        const segmentsKeys = [
            'productive', 'consumer', 'microcredit', 'inmobiliario',
            'consumer_refinanced', 'microcredit_refinanced',
            'consumer_restructured', 'microcredit_restructured',
            'covid_refinanced', 'covid_restructured'
        ];
        
        const colors = {
            productive: 'rgba(0, 43, 91, 0.85)',
            consumer: 'rgba(67, 71, 79, 0.85)',
            microcredit: 'rgba(133, 248, 196, 0.85)',
            inmobiliario: 'rgba(213, 227, 252, 0.85)',
            consumer_refinanced: 'rgba(120, 160, 220, 0.85)',
            microcredit_refinanced: 'rgba(140, 200, 160, 0.85)',
            consumer_restructured: 'rgba(180, 120, 200, 0.85)',
            microcredit_restructured: 'rgba(200, 160, 140, 0.85)',
            covid_refinanced: 'rgba(220, 120, 120, 0.85)',
            covid_restructured: 'rgba(240, 180, 120, 0.85)'
        };
        
        const datasets = segmentsKeys.map(key => {
            const seg = this.results.segments[key];
            const dataVals = [];
            for (let m = 1; m <= 12; m++) {
                dataVals.push(seg.monthly[m] || 0);
            }
            return {
                label: seg.label,
                data: dataVals,
                backgroundColor: colors[key],
                borderColor: colors[key].replace('0.85', '1'),
                borderWidth: 1.5,
                fill: true,
                tension: 0.25
            };
        });
        
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#94a3b8',
                            boxWidth: 10,
                            font: { size: 9 }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.raw);
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8', font: { size: 9 } }
                    },
                    y: {
                        stacked: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 9 },
                            callback: function(value) {
                                return '$' + (value / 1e6).toFixed(1) + 'M';
                            }
                        }
                    }
                }
            }
        });
    }
}" x-init="
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
">

    <!-- Header Section -->
    <div class="flex justify-between items-end mb-8">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant mb-1 block">Datos Base</span>
            <h2 class="text-3xl font-extrabold tracking-tight text-on-surface">Histórico Cartera de Créditos</h2>
            <p class="text-sm text-on-surface-variant mt-2 max-w-lg">Resumen y clasificación de la Cartera de Créditos histórica conforme a la metodología contable (Líneas 39 a 52).</p>
        </div>
        <div class="flex items-center gap-3">
            <label class="text-xs font-semibold text-slate-500">Año Contable:</label>
            <select wire:model.live="selectedYear" class="w-36 border border-outline-variant/30 rounded-md py-2 px-3 text-sm bg-white dark:bg-slate-900 focus:ring-1 focus:ring-primary focus:border-primary transition-all font-medium text-slate-700 dark:text-slate-300 shadow-sm">
                @foreach($availableYears as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
        <!-- Card 1: Cartera Bruta -->
        <div class="bg-surface-container-lowest dark:bg-slate-900/40 rounded-xl p-5 border border-outline-variant/20 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Cartera Bruta</span>
                    <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                </div>
                <h3 class="text-2xl font-black text-on-surface tabular-nums">
                    ${{ number_format($trend['averages']['bruta'], 2) }}
                </h3>
            </div>
            <p class="text-[11px] text-slate-500 mt-3 border-t border-slate-100 dark:border-slate-800/50 pt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-green-500">analytics</span> Promedio mensual ({{ $trend['month_count'] }} {{ $trend['month_count'] == 1 ? 'mes' : 'meses' }})
            </p>
        </div>

        <!-- Card 2: Provisiones -->
        <div class="bg-surface-container-lowest dark:bg-slate-900/40 rounded-xl p-5 border border-outline-variant/20 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Provisiones</span>
                    <span class="material-symbols-outlined text-lg text-error">shield_with_heart</span>
                </div>
                <h3 class="text-2xl font-black text-error tabular-nums">
                    ${{ number_format($trend['averages']['provisiones'], 2) }}
                </h3>
            </div>
            <p class="text-[11px] text-slate-500 mt-3 border-t border-slate-100 dark:border-slate-800/50 pt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-error">analytics</span> Promedio mensual ({{ $trend['month_count'] }} {{ $trend['month_count'] == 1 ? 'mes' : 'meses' }})
            </p>
        </div>

        <!-- Card 3: Por Vencer -->
        <div class="bg-surface-container-lowest dark:bg-slate-900/40 rounded-xl p-5 border border-outline-variant/20 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Cartera por Vencer</span>
                    <span class="material-symbols-outlined text-lg text-[#85f8c4]">check_circle</span>
                </div>
                <h3 class="text-2xl font-black text-on-surface tabular-nums">
                    ${{ number_format($trend['averages']['vencer'], 2) }}
                </h3>
            </div>
            <p class="text-[11px] text-slate-500 mt-3 border-t border-slate-100 dark:border-slate-800/50 pt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-green-500">trending_up</span>
                {{ number_format(($trend['averages']['vencer'] / max(1, $trend['averages']['bruta'])) * 100, 2) }}% de la cartera bruta
            </p>
        </div>

        <!-- Card 4: En Riesgo -->
        <div class="bg-surface-container-lowest dark:bg-slate-900/40 rounded-xl p-5 border border-outline-variant/20 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Cartera en Riesgo</span>
                    <span class="material-symbols-outlined text-lg text-orange-400">report_problem</span>
                </div>
                <h3 class="text-2xl font-black text-orange-500 dark:text-orange-400 tabular-nums">
                    ${{ number_format($trend['averages']['riesgo'], 2) }}
                </h3>
            </div>
            <p class="text-[11px] text-slate-500 mt-3 border-t border-slate-100 dark:border-slate-800/50 pt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-orange-400">warning</span>
                {{ number_format(($trend['averages']['riesgo'] / max(1, $trend['averages']['bruta'])) * 100, 2) }}% índice de morosidad
            </p>
        </div>
    </div>

    <!-- Chart Panel -->
    <div class="bg-slate-950 rounded-xl border border-slate-800/80 p-6 mb-8 shadow-xl">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-sm text-[#85f8c4]">bar_chart</span> Distribución Mensual Histórica por Segmento ($)
        </h3>
        <div class="h-80 w-full relative" wire:ignore>
            <canvas id="portfolioHistoryChart"></canvas>
        </div>
    </div>

    <!-- Monthly Grid Table -->
    <div class="bg-surface-container-lowest dark:bg-slate-900/10 rounded-xl border border-outline-variant/20 shadow-sm overflow-hidden mb-8">
        <div class="p-5 border-b border-outline-variant/20 bg-surface-container-low/30">
            <h3 class="text-sm font-bold text-on-surface">Desglose Histórico Mensual (Líneas 39 a 52 de EEFFun)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead>
                    <tr class="border-b border-outline-variant/20 bg-surface-container-low/50 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4 w-72">Concepto Contable</th>
                        @foreach(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $monthName)
                            <th class="py-3 px-4 text-right">{{ $monthName }}</th>
                        @endforeach
                        <th class="py-3 px-4 text-right bg-primary/10 text-primary dark:text-[#85f8c4]">Total Anual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 text-xs">
                    <!-- CARTERA BRUTA -->
                    <tr class="bg-slate-50 dark:bg-slate-900/50 font-bold border-t-2 border-outline-variant/20">
                        <td class="py-3 px-4 text-on-surface uppercase font-black text-sm">CARTERA DE CRÉDITOS BRUTA</td>
                        @for($m = 1; $m <= 12; $m++)
                            <td class="py-3 px-4 text-right tabular-nums">
                                ${{ number_format($trend['totals']['bruta'][$m], 2) }}
                            </td>
                        @endfor
                        <td class="py-3 px-4 text-right tabular-nums bg-primary/10 text-primary dark:text-[#85f8c4]">
                            ${{ number_format($trend['totals']['total_bruta'], 2) }}
                        </td>
                    </tr>

                    <!-- SEGMENTS LIST -->
                    @php
                        $displayKeys = [
                            'productive', 'consumer', 'microcredit', 'inmobiliario',
                            'consumer_refinanced', 'microcredit_refinanced',
                            'consumer_restructured', 'microcredit_restructured',
                            'covid_refinanced', 'covid_restructured'
                        ];
                    @endphp
                    @foreach($displayKeys as $key)
                        <tr class="hover:bg-surface-container-lowest/50">
                            <td class="py-2.5 px-6 text-slate-700 dark:text-slate-300 font-medium">
                                {{ $trend['segments'][$key]['label'] }}
                            </td>
                            @for($m = 1; $m <= 12; $m++)
                                <td class="py-2.5 px-4 text-right tabular-nums text-slate-600 dark:text-slate-400">
                                    ${{ number_format($trend['segments'][$key]['monthly'][$m], 2) }}
                                </td>
                            @endfor
                            <td class="py-2.5 px-4 text-right tabular-nums font-bold text-slate-800 dark:text-slate-200 bg-slate-50/50 dark:bg-slate-900/10">
                                ${{ number_format($trend['segments'][$key]['total'], 2) }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- PROVISIONES -->
                    <tr class="hover:bg-surface-container-lowest/50 text-error font-medium border-t border-outline-variant/20">
                        <td class="py-2.5 px-4 font-bold uppercase">
                            {{ $trend['segments']['provisiones']['label'] }}
                        </td>
                        @for($m = 1; $m <= 12; $m++)
                            <td class="py-2.5 px-4 text-right tabular-nums">
                                ${{ number_format($trend['segments']['provisiones']['monthly'][$m], 2) }}
                            </td>
                        @endfor
                        <td class="py-2.5 px-4 text-right tabular-nums font-black bg-slate-50/50 dark:bg-slate-900/10">
                            ${{ number_format($trend['segments']['provisiones']['total'], 2) }}
                        </td>
                    </tr>

                    <!-- CARTERA NETA -->
                    <tr class="bg-slate-100 dark:bg-slate-900/70 font-black border-t border-b-2 border-outline-variant/20">
                        <td class="py-3 px-4 uppercase text-sm">CARTERA DE CRÉDITOS NETA</td>
                        @for($m = 1; $m <= 12; $m++)
                            <td class="py-3 px-4 text-right tabular-nums">
                                ${{ number_format($trend['totals']['neta'][$m], 2) }}
                            </td>
                        @endfor
                        <td class="py-3 px-4 text-right tabular-nums bg-primary/15 text-primary dark:text-[#85f8c4]">
                            ${{ number_format($trend['totals']['total_neta'], 2) }}
                        </td>
                    </tr>

                    <!-- TOTAL VENCER -->
                    <tr class="hover:bg-surface-container-lowest/50 font-medium">
                        <td class="py-2.5 px-4 uppercase font-bold text-slate-500">TOTAL CARTERA DE CREDITO POR VENCER</td>
                        @for($m = 1; $m <= 12; $m++)
                            <td class="py-2.5 px-4 text-right tabular-nums text-slate-600 dark:text-slate-400">
                                ${{ number_format($trend['totals']['vencer'][$m], 2) }}
                            </td>
                        @endfor
                        <td class="py-2.5 px-4 text-right tabular-nums font-bold bg-slate-50/50 dark:bg-slate-900/10">
                            ${{ number_format($trend['totals']['total_vencer'], 2) }}
                        </td>
                    </tr>

                    <!-- TOTAL RIESGO -->
                    <tr class="hover:bg-surface-container-lowest/50 font-medium">
                        <td class="py-2.5 px-4 uppercase font-bold text-orange-500 dark:text-orange-400">TOTAL CARTERA DE CREDITO EN RIESGO</td>
                        @for($m = 1; $m <= 12; $m++)
                            <td class="py-2.5 px-4 text-right tabular-nums text-orange-500/80 dark:text-orange-400/80">
                                ${{ number_format($trend['totals']['riesgo'][$m], 2) }}
                            </td>
                        @endfor
                        <td class="py-2.5 px-4 text-right tabular-nums font-bold text-orange-600 dark:text-orange-400 bg-slate-50/50 dark:bg-slate-900/10">
                            ${{ number_format($trend['totals']['total_riesgo'], 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
