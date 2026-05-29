<div class="max-w-full mx-auto">

    {{-- Header --}}
    <div class="mb-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant mb-1 block">
            Presupuesto {{ $budgetYear }}
        </span>
        <h2 class="text-3xl font-extrabold tracking-tight text-on-surface">
            Proyección de Cartera de Crédito
        </h2>
        <p class="text-sm text-on-surface-variant mt-2">
            Cálculo de colocación anual requerida — base
            {{ $baseYear }}-{{ str_pad($baseMes, 2, '0', STR_PAD_LEFT) }}
        </p>
    </div>

    @php
        $fmt  = fn($v, $d=2) => number_format($v, $d, '.', ',');
        $fmtp = fn($v) => number_format($v, 2, '.', ',').'%';
    @endphp

    <div class="overflow-x-auto rounded-lg shadow-sm border border-outline-variant/20">
        <table class="min-w-full text-xs tabular-nums border-collapse">

            {{-- ── ROW 1: group headers ── --}}
            <thead>
                <tr class="bg-[#001736] text-white border-b border-white/10">
                    <th class="sticky left-0 bg-[#001736] px-4 py-3 text-left font-bold uppercase tracking-widest"
                        rowspan="2">Segmento</th>

                    {{-- Datos base --}}
                    <th class="px-3 py-2 text-center font-bold border-r border-white/10" colspan="2">
                        Datos {{ $baseYear }}-{{ str_pad($baseMes, 2, '0', STR_PAD_LEFT) }}
                    </th>

                    {{-- Recuperaciones --}}
                    <th class="px-3 py-2 text-center font-bold border-r border-white/10" colspan="2">
                        Recuperaciones Proyectadas
                    </th>

                    {{-- Colocación --}}
                    <th class="px-3 py-2 text-center font-bold border-r border-white/10" colspan="3">
                        Cálculo de Colocación Anual Requerida
                    </th>

                    {{-- Cartera 2025 --}}
                    <th class="px-3 py-2 text-center font-bold border-r border-white/10" colspan="1">
                        Cartera {{ $baseYear }}
                    </th>

                    {{-- Proyecciones 2026 --}}
                    <th class="px-3 py-2 text-center font-bold border-r border-white/10" colspan="3">
                        Proyección {{ $budgetYear }}-12-31
                    </th>

                    {{-- Promedios --}}
                    <th class="px-3 py-2 text-center font-bold text-[#85f8c4]" colspan="3">
                        Promedios
                    </th>
                </tr>

                {{-- ── ROW 2: column headers ── --}}
                <tr class="bg-[#002050] text-white text-[9px] uppercase tracking-wide">
                    {{-- Base --}}
                    <th class="px-3 py-3 text-right font-semibold min-w-[130px] border-t border-white/10">
                        Saldo Cartera<br/>{{ $baseYear }}-{{ str_pad($baseMes, 2, '0', STR_PAD_LEFT) }}
                    </th>
                    <th class="px-3 py-3 text-right font-semibold min-w-[80px] border-r border-white/10 border-t border-white/10">
                        Particip.<br/>%
                    </th>

                    {{-- Recuperaciones --}}
                    <th class="px-3 py-3 text-right font-semibold min-w-[130px] border-t border-white/10">
                        Recup. Proyect.<br/>Cartera Corte<br/>{{ $baseYear }}-{{ str_pad($baseMes, 2, '0', STR_PAD_LEFT) }}
                    </th>
                    <th class="px-3 py-3 text-right font-semibold min-w-[130px] border-r border-white/10 border-t border-white/10">
                        Recup. Proyect.<br/>Colocación<br/>Ene-Dic {{ $budgetYear }}
                    </th>

                    {{-- Colocación --}}
                    <th class="px-3 py-3 text-right font-semibold min-w-[130px] border-t border-white/10">
                        Colocación<br/>Adicional a<br/>Recup. Proyect.
                    </th>
                    <th class="px-3 py-3 text-right font-semibold min-w-[140px] border-t border-white/10">
                        Coloc. Requerida<br/>con Recup. Cartera<br/>Corte {{ $baseYear }}-12-31
                    </th>
                    <th class="px-3 py-3 text-right font-semibold min-w-[140px] border-r border-white/10 border-t border-white/10">
                        Coloc. Requerida<br/>con Recup.<br/>Proyectada
                    </th>

                    {{-- Cartera originada --}}
                    <th class="px-3 py-3 text-right font-semibold min-w-[130px] border-r border-white/10 border-t border-white/10">
                        Cartera Originada,<br/>Vendida y Adm.<br/>{{ $baseYear }}
                    </th>

                    {{-- Proyecciones --}}
                    <th class="px-3 py-3 text-right font-semibold min-w-[130px] border-t border-white/10">
                        Saldo Cartera<br/>Proyect.<br/>{{ $budgetYear }}-12-31
                    </th>
                    <th class="px-3 py-3 text-right font-semibold min-w-[80px] border-t border-white/10">
                        Particip.<br/>Proyect. %
                    </th>
                    <th class="px-3 py-3 text-right font-semibold min-w-[100px] border-r border-white/10 border-t border-white/10">
                        Comportam.<br/>Proyect. {{ $budgetYear }}
                    </th>

                    {{-- Promedios --}}
                    <th class="px-3 py-3 text-right font-semibold min-w-[70px] text-[#85f8c4] border-t border-white/10">
                        # neg<br/>mes
                    </th>
                    <th class="px-3 py-3 text-right font-semibold min-w-[70px] text-[#85f8c4] border-t border-white/10">
                        # neg<br/>mes BI
                    </th>
                    <th class="px-3 py-3 text-right font-semibold min-w-[70px] text-[#85f8c4] border-t border-white/10">
                        # neg<br/>mes BC
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($rows as $row)
                    <tr class="border-b border-outline-variant/10 hover:bg-surface-container/40 transition-colors">
                        <td class="sticky left-0 bg-surface hover:bg-surface-container/40 px-4 py-3 font-bold text-on-surface uppercase text-[10px] tracking-wide">
                            {{ $row['label'] }}
                        </td>

                        {{-- Saldo base --}}
                        <td class="px-3 py-3 text-right text-on-surface font-medium">
                            {{ $fmt($row['saldo']) }}
                        </td>
                        <td class="px-3 py-3 text-right text-on-surface-variant border-r border-outline-variant/10">
                            {{ $fmtp($row['particip']) }}
                        </td>

                        {{-- Recuperaciones --}}
                        <td class="px-3 py-3 text-right text-on-surface">
                            {{ $fmt($row['recup_cartera']) }}
                        </td>
                        <td class="px-3 py-3 text-right text-on-surface-variant border-r border-outline-variant/10">
                            {{ $row['recup_colocacion'] > 0 ? $fmt($row['recup_colocacion']) : '—' }}
                        </td>

                        {{-- Colocación --}}
                        <td class="px-3 py-3 text-right text-on-surface">
                            {{ $fmt($row['coloc_adicional']) }}
                        </td>
                        <td class="px-3 py-3 text-right font-semibold text-primary">
                            {{ $fmt($row['coloc_req_corte']) }}
                        </td>
                        <td class="px-3 py-3 text-right font-semibold text-primary border-r border-outline-variant/10">
                            {{ $fmt($row['coloc_req_proyect']) }}
                        </td>

                        {{-- Cartera originada --}}
                        <td class="px-3 py-3 text-right text-on-surface border-r border-outline-variant/10">
                            {{ $fmt($row['cart_originada']) }}
                        </td>

                        {{-- Proyecciones --}}
                        <td class="px-3 py-3 text-right text-on-surface font-medium">
                            {{ $fmt($row['saldo_proyect']) }}
                        </td>
                        <td class="px-3 py-3 text-right text-on-surface-variant">
                            {{ $fmtp($row['particip_proyect']) }}
                        </td>
                        <td class="px-3 py-3 text-right border-r border-outline-variant/10
                            {{ $row['comportamiento'] > 0 ? 'text-emerald-600 font-semibold' : ($row['comportamiento'] < 0 ? 'text-red-600 font-semibold' : 'text-on-surface-variant') }}">
                            {{ $fmtp($row['comportamiento']) }}
                        </td>

                        {{-- Promedios --}}
                        <td class="px-3 py-3 text-right text-on-surface-variant">
                            {{ $row['neg_mes'] }}
                        </td>
                        <td class="px-3 py-3 text-right text-on-surface-variant">
                            {{ $row['neg_mes_bi'] ?: '—' }}
                        </td>
                        <td class="px-3 py-3 text-right text-on-surface-variant">
                            {{ $row['neg_mes_bc'] ?: '—' }}
                        </td>
                    </tr>
                @endforeach

                {{-- TOTAL ROW --}}
                <tr class="bg-[#001736] text-white font-bold border-t-2 border-[#85f8c4]">
                    <td class="sticky left-0 bg-[#001736] px-4 py-4 text-[10px] uppercase tracking-widest">TOTAL</td>

                    <td class="px-3 py-4 text-right text-[#85f8c4]">{{ $fmt($totals['saldo']) }}</td>
                    <td class="px-3 py-4 text-right border-r border-white/10">100.00%</td>

                    <td class="px-3 py-4 text-right text-[#85f8c4]">{{ $fmt($totals['recup_cartera']) }}</td>
                    <td class="px-3 py-4 text-right border-r border-white/10">—</td>

                    <td class="px-3 py-4 text-right text-[#85f8c4]">{{ $fmt($totals['coloc_adicional']) }}</td>
                    <td class="px-3 py-4 text-right text-[#85f8c4]">{{ $fmt($totals['coloc_req_corte']) }}</td>
                    <td class="px-3 py-4 text-right text-[#85f8c4] border-r border-white/10">{{ $fmt($totals['coloc_req_proyect']) }}</td>

                    <td class="px-3 py-4 text-right text-[#85f8c4] border-r border-white/10">{{ $fmt($totals['cart_originada']) }}</td>

                    <td class="px-3 py-4 text-right text-[#85f8c4]">{{ $fmt($totals['saldo_proyect']) }}</td>
                    <td class="px-3 py-4 text-right">100.00%</td>
                    <td class="px-3 py-4 text-right border-r border-white/10">—</td>

                    <td class="px-3 py-4 text-right text-[#85f8c4]">{{ $totals['neg_mes'] }}</td>
                    <td class="px-3 py-4 text-right">—</td>
                    <td class="px-3 py-4 text-right">—</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
