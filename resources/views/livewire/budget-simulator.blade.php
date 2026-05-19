<div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex justify-between items-end mb-10">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant mb-1 block">Planificación Financiera</span>
            <h2 class="text-3xl font-extrabold tracking-tight text-on-surface">Simulador de Presupuesto 2026</h2>
            <p class="text-sm text-on-surface-variant mt-2 max-w-lg">Ajusta las premisas macroeconómicas y observa el impacto inmediato en el estado de resultados proyectado a 12 meses.</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="calculate" class="px-6 py-3 bg-primary text-white rounded-md text-sm font-semibold flex items-center gap-2 hover:bg-primary-container hover:text-white transition-colors shadow-lg active:scale-95">
                <span class="material-symbols-outlined text-sm" data-icon="play_arrow">play_arrow</span>
                Generar Proyección
            </button>
        </div>
    </div>
    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-[#fce8e8] text-[#c5221f] rounded-lg border border-[#fad2cf] flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <div>
                <h4 class="font-bold text-sm">Acción Requerida</h4>
                <p class="text-xs mt-1">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Asymmetric Ledger Layout -->
    <div class="grid grid-cols-12 gap-8">
        
        <!-- Editorial Sidebar (35% Side) - INPUTS -->
        <aside class="col-span-4 space-y-6 h-[800px] overflow-y-auto pr-2 custom-scrollbar">
            
            <!-- METAS DE COLOCACIÓN (NUEVA CARTERA ANUAL) -->
            <div class="bg-surface-container-low p-6 rounded-lg shadow-sm border border-outline-variant/20 mt-4">
                <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">trending_up</span> Metas de Colocación Anual ($)
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1">Consumo</label>
                        <input type="number" step="0.01" wire:model="placementConsumer" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary tabular-nums text-right">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1">Microcrédito</label>
                        <input type="number" step="0.01" wire:model="placementMicrocredit" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary tabular-nums text-right">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1">Productiva</label>
                        <input type="number" step="0.01" wire:model="placementProductive" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary tabular-nums text-right">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Refinanciada</label>
                            <input type="number" step="0.01" wire:model="placementRefinanced" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary tabular-nums text-right">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Reestructurada</label>
                            <input type="number" step="0.01" wire:model="placementRestructured" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary tabular-nums text-right">
                        </div>
                    </div>
                </div>
            </div>

            <!-- CRECIMIENTO DE CAPTACIONES -->
            <div class="bg-surface-container-low p-6 rounded-lg shadow-sm border border-outline-variant/20 mt-4">
                <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">account_balance_wallet</span> Crecimiento Captaciones (%)
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1">Dep. Vista</label>
                        <input type="number" step="0.001" wire:model="sightGrowth" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary tabular-nums text-right">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1">Dep. Plazo</label>
                        <input type="number" step="0.001" wire:model="termGrowth" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary tabular-nums text-right">
                    </div>
                </div>
            </div>

            <!-- TASAS DE INTERES -->
            <div class="bg-surface-container-low p-6 rounded-lg shadow-sm border border-outline-variant/20">
                <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">percent</span> Tasas de Interés (Anuales)
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1 uppercase">Activa Prod.</label>
                        <input type="number" step="0.0001" wire:model="productiveRate" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1 uppercase">Activa Cons.</label>
                        <input type="number" step="0.0001" wire:model="consumerRate" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1 uppercase">Microcrédito</label>
                        <input type="number" step="0.0001" wire:model="microcreditRate" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1 uppercase">Pasiva Vista</label>
                        <input type="number" step="0.0001" wire:model="sightRate" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1 uppercase">Pasiva Plazo</label>
                        <input type="number" step="0.0001" wire:model="termRate" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary">
                    </div>
                </div>
            </div>

            <!-- INGRESOS EXTRAORDINARIOS -->
            <div class="bg-surface-container-low p-6 rounded-lg shadow-sm border border-outline-variant/20 mt-4">
                <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-tertiary-container mb-2 flex items-center gap-2 border-outline-variant/20">
                    <span class="material-symbols-outlined text-sm">attach_money</span> Ingresos Extraordinarios
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1">Activos Castigados ($)</label>
                        <input type="number" wire:model="recoveryWrittenOff" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary tabular-nums text-right">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1">Rev. Provisiones ($)</label>
                        <input type="number" wire:model="reversalProvisions" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-md py-1.5 px-2 text-sm focus:ring-1 focus:ring-primary tabular-nums text-right">
                    </div>
                </div>
            </div>

            <!-- GASTOS E INVERSIONES -->
            <div class="bg-primary p-6 rounded-lg text-white shadow-md">
                <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#85f8c4] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">account_balance</span> Presupuesto y Gastos
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-[10px] font-semibold text-white/80 mb-1">Gastos Operativos (Mensual)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1.5 text-white/50">$</span>
                            <input type="number" wire:model="operatingExpenses" class="w-full bg-white/10 border-none rounded-md py-1.5 pl-7 pr-3 text-sm focus:ring-1 focus:ring-[#85f8c4] text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-white/80 mb-1">Inversión Sistemas (Mensual)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1.5 text-white/50">$</span>
                            <input type="number" wire:model="techInvestment" class="w-full bg-white/10 border-none rounded-md py-1.5 pl-7 pr-3 text-sm focus:ring-1 focus:ring-[#85f8c4] text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-white/80 mb-1">Inversión Nueva Imagen (Mensual)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1.5 text-white/50">$</span>
                            <input type="number" wire:model="imageInvestment" class="w-full bg-white/10 border-none rounded-md py-1.5 pl-7 pr-3 text-sm focus:ring-1 focus:ring-[#85f8c4] text-white">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-between items-center text-[10px] text-white/50 uppercase font-bold border-t border-white/10 pt-4">
                    <span>Motor Base</span>
                    <span>V 2.0.0</span>
                </div>
            </div>
        </aside>

        <!-- Main Request List (65% Side) - RESULTS -->
        <div class="col-span-8 space-y-4">
            
            @if(!$results)
                <div class="flex flex-col items-center justify-center h-full bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/40 p-12 text-center">
                    <span class="material-symbols-outlined text-6xl text-slate-300 mb-4" data-icon="monitoring">monitoring</span>
                    <h3 class="text-lg font-bold text-on-surface">Esperando Instrucciones</h3>
                    <p class="text-sm text-on-surface-variant mt-2 max-w-sm">Ajusta los parámetros en el panel izquierdo y haz clic en "Generar Proyección" para calcular el Estado de Resultados.</p>
                </div>
            @else
                <!-- Top Summary Widgets -->
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="bg-[#001736] p-4 rounded-lg shadow-sm border border-outline-variant/10">
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/70">Esfuerzo Colocación Consumo</span>
                        <div class="text-xl font-extrabold text-[#85f8c4] mt-1">
                            ${{ number_format($results['colocacion_requerida']['consumer'], 0) }}
                        </div>
                    </div>
                    <div class="bg-[#001736] p-4 rounded-lg shadow-sm border border-outline-variant/10">
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/70">Esfuerzo Colocación Micro</span>
                        <div class="text-xl font-extrabold text-[#85f8c4] mt-1">
                            ${{ number_format($results['colocacion_requerida']['microcredit'], 0) }}
                        </div>
                    </div>
                    <div class="bg-primary-container p-4 rounded-lg shadow-sm border border-outline-variant/10">
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-primary-container">Colocación Total Requerida</span>
                        <div class="text-xl font-extrabold text-on-primary-container mt-1">
                            ${{ number_format(array_sum($results['colocacion_requerida']), 0) }}
                        </div>
                    </div>
                </div>

                <!-- Table Header -->
                <div class="grid grid-cols-12 gap-2 px-6 py-3 bg-surface-container-low text-[10px] font-bold uppercase tracking-widest text-on-secondary-container rounded-t-lg">
                    <div class="col-span-2">Mes</div>
                    <div class="col-span-2 text-right">Ingresos Fin.</div>
                    <div class="col-span-2 text-right">Otros Ingresos</div>
                    <div class="col-span-2 text-right">Gastos Fin.</div>
                    <div class="col-span-2 text-right">Gastos Fijos Op.</div>
                    <div class="col-span-2 text-right">Utilidad Neta</div>
                </div>

                <!-- Result Rows -->
                <div class="bg-white rounded-b-lg shadow-sm border border-surface-container-low overflow-hidden">
                    @foreach($results['months'] as $index => $mes)
                        <div class="grid grid-cols-12 gap-2 px-6 py-4 {{ $loop->even ? 'bg-surface-container-lowest' : 'bg-surface-container/30' }} hover:bg-surface-container transition-colors items-center border-b border-outline-variant/10 last:border-0">
                            
                            <div class="col-span-2">
                                <h4 class="text-xs font-bold text-on-surface leading-tight">{{ $mes }}</h4>
                            </div>
                            
                            <div class="col-span-2 text-right tabular-nums">
                                <p class="text-xs font-medium text-on-surface">${{ number_format($results['estado_resultados']['ingresos_financieros'][$index], 0) }}</p>
                            </div>

                            <div class="col-span-2 text-right tabular-nums">
                                <p class="text-xs font-medium text-on-tertiary-container">${{ number_format($results['estado_resultados']['otros_ingresos'][$index], 0) }}</p>
                            </div>
                            
                            <div class="col-span-2 text-right tabular-nums">
                                <p class="text-xs font-medium text-error">${{ number_format($results['estado_resultados']['gastos_financieros'][$index], 0) }}</p>
                            </div>

                            <div class="col-span-2 text-right tabular-nums">
                                <p class="text-xs font-medium text-secondary">${{ number_format($results['estado_resultados']['gastos_operativos'][$index], 0) }}</p>
                            </div>

                            <div class="col-span-2 text-right tabular-nums">
                                @php $utilidad = $results['estado_resultados']['utilidad_neta'][$index]; @endphp
                                <p class="text-sm font-bold {{ $utilidad >= 0 ? 'text-on-tertiary-container' : 'text-error' }}">
                                    ${{ number_format($utilidad, 0) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Resumen Total Row -->
                    <div class="grid grid-cols-12 gap-2 px-6 py-5 bg-[#001736] text-white items-center">
                        <div class="col-span-2">
                            <h4 class="text-xs font-bold uppercase tracking-widest text-[#85f8c4]">TOTAL 2026</h4>
                        </div>
                        <div class="col-span-2 text-right tabular-nums text-[11px] font-bold">${{ number_format(array_sum($results['estado_resultados']['ingresos_financieros']), 0) }}</div>
                        <div class="col-span-2 text-right tabular-nums text-[11px] font-bold text-[#85f8c4]">+${{ number_format(array_sum($results['estado_resultados']['otros_ingresos']), 0) }}</div>
                        <div class="col-span-2 text-right tabular-nums text-[11px] font-bold text-red-300">-${{ number_format(array_sum($results['estado_resultados']['gastos_financieros']), 0) }}</div>
                        <div class="col-span-2 text-right tabular-nums text-[11px] font-bold text-red-300">-${{ number_format(array_sum($results['estado_resultados']['gastos_operativos']), 0) }}</div>
                        <div class="col-span-2 text-right tabular-nums text-sm font-extrabold text-[#85f8c4]">${{ number_format(array_sum($results['estado_resultados']['utilidad_neta']), 0) }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #cbd5e1; }
    </style>
</div>
