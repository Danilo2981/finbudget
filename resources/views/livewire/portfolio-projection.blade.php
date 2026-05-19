<div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex justify-between items-end mb-10">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant mb-1 block">Fase 1 de Proyección</span>
            <h2 class="text-3xl font-extrabold tracking-tight text-on-surface">Proyección Cartera de Crédito</h2>
            <p class="text-sm text-on-surface-variant mt-2 max-w-lg">Define tus metas de crecimiento y el sistema calculará automáticamente la colocación requerida.</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="save" class="px-6 py-3 bg-primary text-white rounded-md text-sm font-semibold flex items-center gap-2 hover:bg-primary-container hover:text-white transition-colors shadow-lg active:scale-95">
                <span class="material-symbols-outlined text-sm" data-icon="save">save</span>
                Guardar Proyección
            </button>
        </div>
    </div>

    @if($saved)
    <div class="mb-6 p-4 bg-[#e6f4ea] text-[#137333] rounded-lg border border-[#ceead6] flex items-center gap-3">
        <span class="material-symbols-outlined">check_circle</span>
        <div>
            <h4 class="font-bold text-sm">¡Proyección Guardada Exitosamente!</h4>
            <p class="text-xs mt-1">Los datos se han sincronizado con la base de datos. Ahora puedes ir al <strong>Simulador Presupuestal</strong> para calcular el Estado de Resultados.</p>
        </div>
    </div>
    @endif

    <div class="bg-surface-container-low rounded-lg shadow-sm border border-outline-variant/20 overflow-hidden">
        
        <!-- Table Header -->
        <div class="grid grid-cols-12 gap-2 px-6 py-4 bg-[#001736] text-[10px] font-bold uppercase tracking-widest text-white items-center">
            <div class="col-span-2">Segmento</div>
            <div class="col-span-2 text-right">Saldo Inicial ($)</div>
            <div class="col-span-2 text-right">Recuperaciones Anuales ($)</div>
            <div class="col-span-2 text-right text-[#85f8c4]">Meta Crecimiento (%)</div>
            <div class="col-span-4 text-right text-white">COLOCACIÓN TOTAL REQUERIDA</div>
        </div>

        @php
            $saldosIniciales = [
                'Productivo' => ['key' => 'Productive', 'val' => 5000000],
                'Consumo' => ['key' => 'Consumer', 'val' => 3000000],
                'Microcrédito' => ['key' => 'Microcredit', 'val' => 1000000],
                'Refinanciada' => ['key' => 'Refinanced', 'val' => 500000],
                'Reestructurada' => ['key' => 'Restructured', 'val' => 200000],
            ];
            $totalReq = 0;
            $totalSaldo = 0;
            $totalRecup = 0;
        @endphp

        <!-- Rows -->
        @foreach($saldosIniciales as $label => $data)
            @php
                $key = $data['key'];
                $saldo = $data['val'];
                $reqProp = 'colocacion_requerida';
                $req = $this->colocacion_requerida[strtolower($key)];
                $totalReq += $req;
                $totalSaldo += $saldo;
                $totalRecup += $this->{'recovery' . $key};
            @endphp
            <div class="grid grid-cols-12 gap-2 px-6 py-4 border-b border-outline-variant/10 hover:bg-surface-container/30 items-center transition-colors">
                <div class="col-span-2 font-bold text-sm text-on-surface uppercase">{{ $label }}</div>
                
                <div class="col-span-2 text-right font-medium text-sm tabular-nums text-on-surface-variant">
                    ${{ number_format($saldo, 0) }}
                </div>
                
                <div class="col-span-2 text-right">
                    <input type="number" step="0.01" wire:model.live="recovery{{ $key }}" class="w-full bg-white border border-outline-variant/50 rounded py-1 px-2 text-sm text-right focus:ring-1 focus:ring-primary tabular-nums">
                </div>
                
                <div class="col-span-2 text-right">
                    <input type="number" step="0.01" wire:model.live="targetGrowth{{ $key }}" class="w-full bg-[#f0fdf4] border border-[#bbf7d0] rounded py-1 px-2 text-sm text-right text-[#166534] font-bold focus:ring-1 focus:ring-primary tabular-nums">
                </div>
                
                <div class="col-span-4 text-right font-extrabold text-lg tabular-nums text-primary">
                    ${{ number_format($req, 2) }}
                </div>
            </div>
        @endforeach

        <!-- Footer Totals -->
        <div class="grid grid-cols-12 gap-2 px-6 py-4 bg-surface-container text-on-surface items-center font-bold">
            <div class="col-span-2 uppercase text-xs tracking-widest">TOTALES</div>
            <div class="col-span-2 text-right text-sm tabular-nums">${{ number_format($totalSaldo, 0) }}</div>
            <div class="col-span-2 text-right text-sm tabular-nums">${{ number_format($totalRecup, 2) }}</div>
            <div class="col-span-2 text-right text-sm text-on-surface-variant">--</div>
            <div class="col-span-4 text-right text-2xl tabular-nums text-primary">
                ${{ number_format($totalReq, 2) }}
            </div>
        </div>
    </div>
</div>
