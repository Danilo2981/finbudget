<div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex justify-between items-end mb-10">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant mb-1 block">Datos Base</span>
            <h2 class="text-3xl font-extrabold tracking-tight text-on-surface">Histórico Financiero</h2>
            <p class="text-sm text-on-surface-variant mt-2 max-w-lg">Carga y administra los estados financieros históricos desde tu hoja base.</p>
        </div>
        <div class="flex items-start gap-3">
            <button wire:click="create" class="px-4 py-2 bg-white text-primary border border-outline-variant/30 rounded-md text-sm font-semibold flex items-center gap-2 hover:bg-surface-container-low transition-colors shadow-sm">
                <span class="material-symbols-outlined text-sm" data-icon="add">add</span>
                Nuevo Registro
            </button>
            <div class="flex flex-col items-center">
                <div class="relative overflow-hidden inline-block">
                    <button class="px-6 py-3 bg-primary text-white rounded-md text-sm font-semibold flex items-center gap-2 hover:bg-primary-container hover:text-white transition-colors shadow-lg active:scale-95 cursor-pointer">
                        <span class="material-symbols-outlined text-sm" data-icon="upload_file">upload_file</span>
                        Cargar Excel Base
                    </button>
                    <input type="file" wire:model="excelFile" class="absolute left-0 top-0 right-0 bottom-0 opacity-0 cursor-pointer w-full h-full" accept=".xlsx,.xls,.csv" />
                </div>
                <button wire:click="downloadTemplate" class="mt-2 text-[11px] font-bold text-primary underline decoration-primary/50 hover:decoration-primary hover:text-primary-container transition-all">
                    ¿No tienes el formato? Descargar plantilla
                </button>
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-[#e8fce8] text-[#1f8c44] rounded-lg border border-[#cffad2] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined">check_circle</span>
                <p class="text-xs font-semibold">{{ session('message') }}</p>
            </div>
            <button type="button" class="text-[#1f8c44]" onclick="this.parentElement.remove()">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-[#fce8e8] text-[#c5221f] rounded-lg border border-[#fad2cf] flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <p class="text-xs font-semibold">{{ session('error') }}</p>
        </div>
    @endif
    
    @error('excelFile')
        <div class="mb-6 p-4 bg-[#fce8e8] text-[#c5221f] rounded-lg border border-[#fad2cf] flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <p class="text-xs font-semibold">{{ $message }}</p>
        </div>
    @enderror

    <div wire:loading wire:target="excelFile" class="mb-6 p-4 bg-[#e8f4fc] text-[#1f6a8c] rounded-lg border border-[#cfecfa] flex items-center gap-3 w-full">
        <span class="material-symbols-outlined animate-spin">refresh</span>
        <p class="text-xs font-semibold">Procesando archivo Excel, por favor espera...</p>
    </div>

    <!-- Conflict Warning Modal -->
    @if($showConflictWarning)
        <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-surface rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-outline-variant/20 animate-in fade-in zoom-in duration-200">
                <div class="bg-error-container/20 p-6 flex flex-col items-center text-center border-b border-error/10">
                    <div class="w-16 h-16 bg-error-container rounded-full flex items-center justify-center mb-4 text-error shadow-inner">
                        <span class="material-symbols-outlined text-3xl">warning</span>
                    </div>
                    <h3 class="text-xl font-extrabold text-on-surface mb-2">Advertencia de Conflicto</h3>
                    <p class="text-sm text-on-surface-variant max-w-sm">
                        Se detectaron <strong class="text-error">{{ $conflictCount }} registros</strong> en tu archivo que ya existen en la base de datos (mismo código de cuenta, mes y año).
                    </p>
                </div>
                <div class="p-6 bg-surface-container-lowest">
                    <p class="text-xs text-slate-500 mb-6 text-center">
                        Si decides continuar, los datos existentes que coincidan serán eliminados y reemplazados por los nuevos del Excel. Los datos que no coincidan se mantendrán intactos.
                    </p>
                    <div class="flex gap-3 justify-center">
                        <button wire:click="cancelUpload" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-on-surface border border-outline-variant/30 hover:bg-surface-variant transition-colors w-1/2">
                            Cancelar Carga
                        </button>
                        <button wire:click="confirmUpload(true)" class="px-5 py-2.5 rounded-lg text-sm font-semibold bg-error text-on-error hover:bg-on-error-container transition-colors shadow-md w-1/2 flex justify-center items-center gap-2">
                            <span class="material-symbols-outlined text-sm">find_replace</span>
                            Sí, Reemplazar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 overflow-hidden">
        
        <!-- Search & Filter Bar -->
        <div class="p-4 border-b border-outline-variant/20 bg-surface-container-low/30 flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-4 flex-1">
                <div class="relative w-72">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por código o cuenta..." class="w-full pl-9 pr-4 py-2 bg-white border border-outline-variant/30 rounded-md text-sm focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                </div>
                
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-500">Año:</label>
                    <select wire:model.live="selectedYear" class="w-32 border border-outline-variant/30 rounded-md py-2 px-3 text-sm bg-white focus:ring-1 focus:ring-primary focus:border-primary transition-all font-medium text-slate-700 shadow-sm">
                        @if($availableYears->isEmpty())
                            <option value="">N/A</option>
                        @else
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-500">Mes:</label>
                    <select wire:model.live="selectedMonth" class="w-48 border border-outline-variant/30 rounded-md py-2 px-3 text-sm bg-white focus:ring-1 focus:ring-primary focus:border-primary transition-all font-medium text-slate-700 shadow-sm">
                        <option value="all" class="font-bold text-primary">Σ Todo el año (Suma)</option>
                        @foreach($availableMonths as $month)
                            @php
                                $monthNames = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                $monthName = $monthNames[(int)$month] ?? $month;
                            @endphp
                            <option value="{{ $month }}">{{ $monthName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-500">Tipo:</label>
                    <select wire:model.live="selectedTipo" class="w-24 border border-outline-variant/30 rounded-md py-2 px-3 text-sm bg-white focus:ring-1 focus:ring-primary focus:border-primary transition-all font-medium text-slate-700 shadow-sm">
                        <option value="all">Todos</option>
                        @foreach($availableTipos as $tipo)
                            <option value="{{ $tipo }}">{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            @if($records->count() > 0)
            <button wire:click="deleteAll" wire:confirm="¿Estás seguro de que deseas eliminar TODOS los registros históricos? Esta acción no se puede deshacer." class="text-xs text-error hover:text-on-error-container font-semibold flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">delete_sweep</span> Vaciar Datos
            </button>
            @endif
        </div>

        <!-- Form Modal/Inline -->
        @if($showForm)
        <div class="p-6 bg-surface-container-low border-b border-outline-variant/20">
            <h3 class="text-sm font-bold text-on-surface mb-4">{{ $isEditing ? 'Editar Registro' : 'Nuevo Registro' }}</h3>
            <div class="grid grid-cols-4 gap-4">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 mb-1">Nivel</label>
                    <input type="number" wire:model="nivel" class="w-full border border-outline-variant/50 rounded-md py-1.5 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 mb-1">Tipo</label>
                    <input type="text" wire:model="tipo" class="w-full border border-outline-variant/50 rounded-md py-1.5 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 mb-1">Código</label>
                    <input type="text" wire:model="codigo" class="w-full border border-outline-variant/50 rounded-md py-1.5 px-3 text-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-semibold text-slate-500 mb-1">Cuenta</label>
                    <input type="text" wire:model="cuenta" class="w-full border border-outline-variant/50 rounded-md py-1.5 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 mb-1">Mes</label>
                    <input type="number" wire:model="mes" class="w-full border border-outline-variant/50 rounded-md py-1.5 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 mb-1">Año</label>
                    <input type="number" wire:model="año" class="w-full border border-outline-variant/50 rounded-md py-1.5 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 mb-1">Fecha</label>
                    <input type="date" wire:model="fecha" class="w-full border border-outline-variant/50 rounded-md py-1.5 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 mb-1">Saldo</label>
                    <input type="number" step="0.01" wire:model="saldo" class="w-full border border-outline-variant/50 rounded-md py-1.5 px-3 text-sm text-right tabular-nums">
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button wire:click="cancel" class="px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-variant rounded-md transition-colors">Cancelar</button>
                <button wire:click="save" class="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-md hover:bg-primary-container transition-colors">Guardar</button>
            </div>
        </div>
        @endif

        <!-- Data Table (Hierarchical Tree) -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/20 bg-surface-container-low/50">
                        <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 w-24">Código</th>
                        <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Cuenta</th>
                        <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Nivel</th>
                        <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Tipo</th>
                        <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-right">Saldo</th>
                        <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody x-data="{ 
                    collapsed: {},
                    isHidden(codigo) {
                        if (!codigo) return false;
                        let str = String(codigo);
                        // Check all possible parent prefixes
                        for (let i = 1; i < str.length; i++) {
                            if (this.collapsed[str.substring(0, i)]) {
                                return true;
                            }
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
                            // Indent multiplier: 0.75rem per level (we assume 1 level = 1 char approx, but it's 1,2,4,6,8)
                            $indent = max(0, ($codeLength - 1) * 0.75);
                            $isTopLevel = $codeLength <= 2;
                        @endphp
                        <tr x-show="!isHidden('{{ $record->codigo }}')" x-transition.opacity 
                            class="hover:bg-surface-container-lowest/50 transition-colors {{ $isTopLevel ? 'bg-surface-container-low/20 font-semibold' : '' }}">
                            
                            <td class="py-2.5 px-4 font-mono text-xs text-slate-600">{{ $record->codigo }}</td>
                            
                            <td class="py-2.5 px-4 text-on-surface truncate max-w-[300px]" title="{{ $record->cuenta }}">
                                <div class="flex items-center" style="padding-left: {{ $indent }}rem">
                                    @if($record->is_parent)
                                        <button @click="toggle('{{ $record->codigo }}')" class="mr-2 flex-shrink-0 w-5 h-5 flex items-center justify-center rounded-sm bg-surface-variant/40 text-slate-500 hover:bg-surface-variant hover:text-primary transition-colors focus:outline-none">
                                            <span class="material-symbols-outlined text-[16px] transition-transform duration-200" :class="collapsed['{{ $record->codigo }}'] ? '-rotate-90' : 'rotate-0'">expand_more</span>
                                        </button>
                                    @else
                                        <!-- Spacer to align non-parents with parents -->
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
                            
                            <td class="py-2.5 px-4 text-right space-x-2">
                                @if($record->mes !== 'all')
                                    <button wire:click="edit({{ $record->id }})" class="text-slate-400 hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>
                                    <button wire:click="delete({{ $record->id }})" wire:confirm="¿Eliminar este registro?" class="text-slate-400 hover:text-error transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 italic" title="No se puede editar una agrupación anual">Agrupado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-surface-variant/50 mb-3 text-slate-400">
                                    <span class="material-symbols-outlined">account_tree</span>
                                </div>
                                <h3 class="text-sm font-semibold text-on-surface">No hay datos para este período</h3>
                                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Selecciona otro período en el filtro o sube un nuevo archivo Excel.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
