<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="flex justify-between items-end mb-10">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant mb-1 block">
                Presupuesto {{ $budgetYear }}
            </span>
            <h2 class="text-3xl font-extrabold tracking-tight text-on-surface">
                Parámetros de Proyección
            </h2>
            <p class="text-sm text-on-surface-variant mt-2">
                Saldo base:
                <span class="font-semibold">{{ $baseYear }}-{{ str_pad($baseMes, 2, '0', STR_PAD_LEFT) }}</span>
                — Define el porcentaje de crecimiento para calcular la colocación adicional requerida.
            </p>
        </div>
        <button wire:click="save"
            class="px-6 py-3 bg-primary text-white rounded-md text-sm font-semibold flex items-center gap-2 hover:opacity-90 transition shadow-lg active:scale-95">
            <span class="material-symbols-outlined text-sm">save</span>
            Guardar
        </button>
    </div>

    @php $av = $this->autoVolume; @endphp

    {{-- ── Tabla: Volumen Colocación Cartera Automotriz ── --}}
    <div class="bg-surface-container-low rounded-lg shadow-sm border border-outline-variant/20 overflow-hidden mb-8">

        {{-- Header de la tabla --}}
        <div class="grid grid-cols-3 px-6 py-4 bg-[#001736] text-[10px] font-bold uppercase tracking-widest text-white">
            <div class="col-span-1">Volumen (Colocación Cartera de Crédito) Automotriz</div>
            <div class="col-span-1 text-right text-[#85f8c4]">Volumen Adicional</div>
            <div class="col-span-1 text-right">Meses</div>
        </div>

        {{-- Fila 1: Número de créditos por Ejecutivo --}}
        <div class="grid grid-cols-3 px-6 py-3 border-b border-outline-variant/10 hover:bg-surface-container/30 items-center transition-colors">
            <div class="col-span-1 text-sm text-on-surface">Número de créditos por Ejecutivo de Negocios</div>
            <div class="col-span-1 text-right">
                <input type="number" min="1" wire:model.live="autoCreditsPerExec"
                    class="w-32 bg-white border border-outline-variant/50 rounded py-1 px-2 text-sm text-right tabular-nums focus:ring-1 focus:ring-primary">
            </div>
            <div class="col-span-1"></div>
        </div>

        {{-- Fila 2: Número de Ejecutivos --}}
        <div class="grid grid-cols-3 px-6 py-3 border-b border-outline-variant/10 hover:bg-surface-container/30 items-center transition-colors">
            <div class="col-span-1 text-sm text-on-surface">Número de Ejecutivos de Negocios</div>
            <div class="col-span-1 text-right">
                <input type="number" min="1" wire:model.live="autoExecCount"
                    class="w-32 bg-white border border-outline-variant/50 rounded py-1 px-2 text-sm text-right tabular-nums focus:ring-1 focus:ring-primary">
            </div>
            <div class="col-span-1"></div>
        </div>

        {{-- Fila 3: Valor promedio por crédito --}}
        <div class="grid grid-cols-3 px-6 py-3 border-b border-outline-variant/10 hover:bg-surface-container/30 items-center transition-colors">
            <div class="col-span-1 text-sm text-on-surface">Valor promedio por crédito</div>
            <div class="col-span-1 text-right">
                <input type="number" min="1" wire:model.live="autoAvgCreditValue"
                    class="w-40 bg-white border border-outline-variant/50 rounded py-1 px-2 text-sm text-right tabular-nums focus:ring-1 focus:ring-primary">
            </div>
            <div class="col-span-1"></div>
        </div>

        {{-- Fila 4: Productividad mensual promedio (= créditos × ejecutivos × valor) --}}
        <div class="grid grid-cols-3 px-6 py-3 border-b border-outline-variant/10 hover:bg-surface-container/30 items-center transition-colors">
            <div class="col-span-1 text-sm text-on-surface">Productividad mensual promedio</div>
            <div class="col-span-1 text-right font-semibold text-primary tabular-nums text-sm">
                {{ number_format($av['productividad'], 2, '.', ',') }}
            </div>
            <div class="col-span-1 text-right flex flex-col items-end gap-1">
                <input type="number" min="1" max="12" wire:model.live="autoMonths"
                    class="w-20 bg-white border border-outline-variant/50 rounded py-1 px-2 text-sm text-right tabular-nums focus:ring-1 focus:ring-primary">
                <span class="text-[10px] text-on-surface-variant uppercase tracking-widest">meses</span>
            </div>
        </div>

        {{-- Fila 5: TOTALES (= productividad × meses) --}}
        <div class="grid grid-cols-3 px-6 py-3 border-b border-outline-variant/10 bg-surface-container/20 items-center">
            <div class="col-span-1 text-sm font-bold text-on-surface uppercase tracking-wide">TOTALES</div>
            <div class="col-span-1 text-right font-extrabold text-lg text-primary tabular-nums">
                {{ number_format($av['total'], 2, '.', ',') }}
            </div>
            <div class="col-span-1"></div>
        </div>

        {{-- Fila 6: Número total de operaciones --}}
        <div class="grid grid-cols-3 px-6 py-3 border-b border-outline-variant/10 hover:bg-surface-container/30 items-center transition-colors">
            <div class="col-span-1 text-sm text-on-surface">Número total de operaciones</div>
            <div class="col-span-1 text-right font-semibold text-on-surface tabular-nums text-sm">
                {{ number_format($av['totalOps'], 0, '.', ',') }}
            </div>
            <div class="col-span-1"></div>
        </div>

        {{-- Fila 7: Número de operaciones promedio mensuales --}}
        <div class="grid grid-cols-3 px-6 py-3 hover:bg-surface-container/30 items-center transition-colors">
            <div class="col-span-1 text-sm text-on-surface">Número de operaciones promedio mensuales</div>
            <div class="col-span-1 text-right font-semibold text-on-surface tabular-nums text-sm">
                {{ number_format($av['avgMonthlyOps'], 0, '.', ',') }}
            </div>
            <div class="col-span-1"></div>
        </div>

    </div>

    <div class="bg-surface-container-low rounded-lg shadow-sm border border-outline-variant/20 overflow-hidden">

        {{-- Table header --}}
        <div class="grid grid-cols-12 gap-0 px-6 py-4 bg-[#001736] text-[10px] font-bold uppercase tracking-widest text-white items-center">
            <div class="col-span-3">Segmento Crédito</div>
            <div class="col-span-4 text-right">Productividad Periódica Promedio ($)</div>
            <div class="col-span-2 text-right text-[#85f8c4]">Porcentaje Adicional</div>
            <div class="col-span-3 text-right">Colocación Adicional<br/>a la Recup. Proyectada ($)</div>
        </div>

        @php $totalColoc = 0; @endphp

        @foreach($this->rows as $row)
            @php
                $totalColoc += $row['coloc_adicional'];
                $propName = 'targetGrowth' . ucfirst($row['key']);
            @endphp
            <div class="grid grid-cols-12 gap-0 px-6 py-4 border-b border-outline-variant/10 hover:bg-surface-container/30 items-center transition-colors">

                <div class="col-span-3 font-bold text-sm text-on-surface uppercase tracking-wide">
                    {{ $row['label'] }}
                </div>

                <div class="col-span-4 text-right text-sm tabular-nums text-on-surface-variant font-medium">
                    {{ number_format($row['productividad'], 2, '.', ',') }}
                </div>

                <div class="col-span-2 text-right px-2">
                    <input type="number" step="0.0001" min="0" max="5"
                        wire:model.live="{{ $propName }}"
                        class="w-full bg-[#f0fdf4] border border-[#bbf7d0] rounded py-1 px-3 text-sm text-right text-[#166534] font-bold focus:ring-1 focus:ring-primary tabular-nums">
                </div>

                <div class="col-span-3 text-right font-extrabold text-base tabular-nums text-primary">
                    {{ number_format($row['coloc_adicional'], 2, '.', ',') }}
                </div>

            </div>
        @endforeach

        {{-- Totals --}}
        <div class="grid grid-cols-12 gap-0 px-6 py-4 bg-surface-container font-bold items-center">
            <div class="col-span-3 text-xs uppercase tracking-widest text-on-surface">TOTAL</div>
            <div class="col-span-4"></div>
            <div class="col-span-2"></div>
            <div class="col-span-3 text-right text-2xl tabular-nums text-primary">
                {{ number_format($totalColoc, 2, '.', ',') }}
            </div>
        </div>

    </div>
</div>
