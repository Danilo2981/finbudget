<div class="h-full flex flex-col bg-surface-container-lowest">
    <div class="p-6 border-b border-outline-variant/20 bg-surface-container-low/50 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-on-surface tracking-tight">Presupuesto Maestro {{ $budgetYear }}</h2>
            <p class="text-sm text-slate-500 mt-1 font-medium">Proyección estadística basada en históricos (ESF, PYG, CO)</p>
        </div>
        <div class="flex flex-col items-end">
            <button wire:click="generateBudget" wire:loading.attr="disabled" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-container hover:text-on-primary-container transition-all shadow-md flex items-center gap-2">
                <span wire:loading.remove wire:target="generateBudget" class="material-symbols-outlined text-[18px]">auto_graph</span>
                <span wire:loading wire:target="generateBudget" class="material-symbols-outlined text-[18px] animate-spin">refresh</span>
                <span wire:loading.remove wire:target="generateBudget">Generar Proyección {{ $budgetYear }}</span>
                <span wire:loading wire:target="generateBudget">Calculando Estadística...</span>
            </button>
            <button wire:click="$set('showMathModal', true)" class="text-[11px] text-primary hover:underline mt-1.5 font-medium flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px]">help</span> ¿Cómo se calcula matemáticamente?
            </button>
        </div>
    </div>

    <div class="flex-1 flex flex-col min-h-0">
        @if (session()->has('message'))
            <div class="px-6 py-3 bg-primary-container/20 border-b border-primary/10 text-primary text-sm font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="px-6 py-3 bg-error-container/20 border-b border-error/10 text-error text-sm font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined">error</span>
                {{ session('error') }}
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="p-4 border-b border-outline-variant/20 bg-surface-container-low/30 flex flex-wrap gap-4 items-center">
            <div class="relative w-72">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar cuenta..." class="w-full pl-9 pr-4 py-2 bg-white border border-outline-variant/30 rounded-md text-sm focus:ring-1 focus:ring-primary focus:border-primary transition-all">
            </div>
            
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-500">Mes ({{ $budgetYear }}):</label>
                <select wire:model.live="selectedMonth" class="w-48 border border-outline-variant/30 rounded-md py-2 px-3 text-sm bg-white focus:ring-1 focus:ring-primary focus:border-primary transition-all font-medium text-slate-700 shadow-sm">
                    <option value="all" class="font-bold text-primary">Σ Resumen Anual</option>
                    @foreach($availableMonths as $month)
                        @php
                            $monthNames = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                            $monthName = $monthNames[(int)$month] ?? $month;
                        @endphp
                        <option value="{{ $month }}">{{ $monthName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Tree View Table -->
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/20 bg-surface-container-low/50 sticky top-0 z-10">
                        <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 w-24">Código</th>
                        <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Cuenta</th>
                        <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Nivel</th>
                        <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Tipo</th>
                        <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-right">Saldo Proyectado</th>
                    </tr>
                </thead>
                <tbody x-data="{ 
                    collapsed: {},
                    isHidden(codigo) {
                        if (!codigo) return false;
                        let str = String(codigo);
                        for (let i = 1; i < str.length; i++) {
                            if (this.collapsed[str.substring(0, i)]) return true;
                        }
                        return false;
                    },
                    toggle(codigo) {
                        if (this.collapsed[codigo]) {
                            delete this.collapsed[codigo];
                        } else {
                            this.collapsed[codigo] = true;
                        }
                    }
                }" class="divide-y divide-outline-variant/10 text-sm">
                    @forelse ($records as $record)
                        @php
                            $codeLength = strlen((string)$record->codigo);
                            $indent = max(0, ($codeLength - 1) * 0.75);
                            $isTopLevel = $codeLength <= 2;
                        @endphp
                        <tr x-show="!isHidden('{{ $record->codigo }}')" x-transition.opacity 
                            class="hover:bg-surface-container-lowest/50 transition-colors {{ $isTopLevel ? 'bg-surface-container-low/20 font-semibold' : '' }}">
                            
                            <td class="py-2.5 px-4 font-mono text-xs text-slate-600">{{ $record->codigo }}</td>
                            
                            <td class="py-2.5 px-4 text-on-surface truncate max-w-[400px]" title="{{ $record->cuenta }}">
                                <div class="flex items-center" style="padding-left: {{ $indent }}rem">
                                    @if($record->is_parent)
                                        <button @click="toggle('{{ $record->codigo }}')" class="mr-2 flex-shrink-0 w-5 h-5 flex items-center justify-center rounded-sm bg-surface-variant/40 text-slate-500 hover:bg-surface-variant hover:text-primary transition-colors focus:outline-none">
                                            <span class="material-symbols-outlined text-[16px] transition-transform duration-200" :class="collapsed['{{ $record->codigo }}'] ? '-rotate-90' : 'rotate-0'">expand_more</span>
                                        </button>
                                    @else
                                        <div class="w-5 mr-2 flex-shrink-0"></div>
                                    @endif
                                    <span class="{{ $isTopLevel ? 'text-on-surface font-bold' : 'text-slate-700' }}">
                                        {{ $record->cuenta }}
                                    </span>
                                </div>
                            </td>
                            
                            <td class="py-2.5 px-4 text-slate-500 text-xs">{{ $record->nivel }}</td>
                            
                            <td class="py-2.5 px-4 text-slate-600 font-medium">
                                <span class="bg-surface-variant/50 px-2 py-0.5 rounded text-[10px]">{{ $record->tipo }}</span>
                            </td>
                            
                            <td class="py-2.5 px-4 text-right tabular-nums {{ $isTopLevel ? 'font-bold' : 'font-medium' }} {{ $record->saldo < 0 ? 'text-error' : 'text-primary' }}">
                                ${{ number_format($record->saldo, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-container/30 mb-4 text-primary">
                                    <span class="material-symbols-outlined text-3xl">query_stats</span>
                                </div>
                                <h3 class="text-base font-bold text-on-surface mb-2">Presupuesto No Generado</h3>
                                <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">Aún no se ha calculado el presupuesto {{ $budgetYear }}. Haz clic en el botón superior para proyectar los datos históricos estadísticamente.</p>
                                <button wire:click="generateBudget" wire:loading.attr="disabled" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-container hover:text-on-primary-container transition-all shadow-md inline-flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">auto_graph</span>
                                    Generar Ahora
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Math Explanation Modal -->
    @if($showMathModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-data="{}" @keydown.escape.window="$wire.set('showMathModal', false)">
        <div class="bg-surface rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="px-6 py-4 border-b border-outline-variant/20 flex justify-between items-center bg-surface-container-low/50">
                <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">functions</span>
                    Modelo Estadístico de Proyección
                </h3>
                <button wire:click="$set('showMathModal', false)" class="text-slate-400 hover:text-error transition-colors rounded-full p-1 hover:bg-error-container/20">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto text-sm text-slate-700 space-y-6">
                <div>
                    <h4 class="font-bold text-on-surface mb-2 flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs">1</span> Extracción de Cuentas Operativas (Hojas)</h4>
                    <p>El sistema primero escanea tu catálogo y aísla exclusivamente las cuentas de último nivel (las que no tienen subcuentas). Esto asegura que no inflemos los números calculando tendencias sobre cuentas agrupadoras.</p>
                </div>

                <div>
                    <h4 class="font-bold text-on-surface mb-2 flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs">2</span> Modelo de Regresión Lineal (Mínimos Cuadrados)</h4>
                    <p>Para cada una de estas cuentas, el algoritmo lee todos tus saldos históricos mes a mes y los ubica en un plano cartesiano (donde <code class="bg-surface-variant px-1.5 py-0.5 rounded text-xs text-primary">X = Mes continuo</code> y <code class="bg-surface-variant px-1.5 py-0.5 rounded text-xs text-primary">Y = Saldo</code>). Luego, aplica la fórmula de Mínimos Cuadrados para encontrar la línea de tendencia exacta:</p>
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mt-3 font-mono text-xs text-slate-600 flex flex-col gap-2">
                        <div><span class="font-bold text-primary">Ecuación:</span> Y = mX + b</div>
                        <div><span class="font-bold text-primary">Pendiente (m):</span> ((n * Σxy) - (Σx * Σy)) / ((n * Σx²) - (Σx)²)</div>
                        <div><span class="font-bold text-primary">Intersección (b):</span> (Σy - (m * Σx)) / n</div>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-on-surface mb-2 flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs">3</span> Proyección Futura</h4>
                    <p>Con la pendiente exacta de crecimiento o decrecimiento de cada cuenta, el algoritmo evalúa la fórmula para los 12 meses futuros (Ej. Enero {{ $budgetYear }} = Mes continuo 73) obteniendo el saldo matemático proyectado para cada periodo.</p>
                </div>

                <div>
                    <h4 class="font-bold text-on-surface mb-2 flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs">4</span> Consolidación Contable</h4>
                    <p>Finalmente, habiendo proyectado el futuro de las cuentas operativas, el sistema suma los valores desde el nivel más bajo (ej. 8) hacia arriba (6, 4, 2, 1) para cada uno de los 12 meses. Así se reconstruyen los totales de Activo, Pasivo, Patrimonio, Ingreso y Gasto, garantizando integridad matemática y contable.</p>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-outline-variant/20 bg-surface-container-low/50 flex justify-end">
                <button wire:click="$set('showMathModal', false)" class="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-md hover:bg-primary-container transition-colors shadow-sm">Entendido</button>
            </div>
        </div>
    </div>
    @endif
</div>
