<?php

namespace App\Livewire;

use App\Models\FinancialHistory;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class HistoricalFinancials extends Component
{
    use WithFileUploads, WithPagination;

    public $excelFile;
    public $search = '';
    
    // Edit Form state
    public $editId = null;
    public $nivel, $tipo, $codigo, $cuenta, $mes, $año, $fecha, $saldo;
    public $isEditing = false;
    public $showForm = false;

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = ['nivel', 'tipo', 'codigo', 'cuenta', 'mes', 'año', 'fecha', 'saldo'];
        
        foreach ($headers as $index => $header) {
            $column = chr(65 + $index); // A, B, C, etc.
            $sheet->setCellValue($column . '1', $header);
            
            // Basic styling for header
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Add a sample row (optional)
        $sheet->setCellValue('A2', '1');
        $sheet->setCellValue('B2', 'ACTIVO');
        $sheet->setCellValue('C2', '110000');
        $sheet->setCellValue('D2', 'FONDOS DISPONIBLES');
        $sheet->setCellValue('E2', '1');
        $sheet->setCellValue('F2', '2025');
        $sheet->setCellValue('G2', '2025-01-31');
        $sheet->setCellValue('H2', '15000.50');

        $writer = new Xlsx($spreadsheet);
        
        $fileName = 'plantilla_historico_base.xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempPath);
        
        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    protected $rules = [
        'nivel' => 'nullable|integer',
        'tipo' => 'nullable|string|max:255',
        'codigo' => 'nullable|string|max:255',
        'cuenta' => 'nullable|string|max:255',
        'mes' => 'nullable|integer',
        'año' => 'nullable|integer',
        'fecha' => 'nullable|date',
        'saldo' => 'nullable|numeric',
    ];

    public $uploadId = null;
    public $showConflictWarning = false;
    public $conflictCount = 0;
    
    public $selectedYear = '';
    public $selectedMonth = '';
    public $selectedTipo = 'all';

    public function mount()
    {
        $latestPeriod = FinancialHistory::select('año', 'mes')
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->first();
            
        if ($latestPeriod) {
            $this->selectedYear = $latestPeriod->año;
            $this->selectedMonth = $latestPeriod->mes;
        }
    }

    public function updatingSearch()
    {
        // No pagination reset needed
    }

    public function updatedExcelFile()
    {
        $this->uploadExcel();
    }
    
    public function updatedSelectedYear()
    {
        // When year changes, reset month to 'all' or latest available
        $latestMonth = FinancialHistory::where('año', $this->selectedYear)->max('mes');
        $this->selectedMonth = $latestMonth ?? 'all';
    }

    public function uploadExcel()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $path = $this->excelFile->getRealPath();
            // Store the path so we can read it again in confirmUpload if needed
            $this->tempFilePath = $path;
            
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $highestRow = $worksheet->getHighestDataRow();
            $highestColumn = 'H'; // We only care up to column H (saldo)
            
            $rows = $worksheet->rangeToArray(
                'A1:' . $highestColumn . $highestRow,
                null,
                true,
                false, // Don't format, get raw values
                false  // Don't index by column letter
            );
            
            $parsedData = [];
            $headerSkipped = false;
            
            foreach ($rows as $data) {
                if (!$headerSkipped) {
                    $headerSkipped = true;
                    continue;
                }
                
                // Skip if both codigo and cuenta are literally null or empty strings
                if ((!isset($data[2]) || trim((string)$data[2]) === '') && 
                    (!isset($data[3]) || trim((string)$data[3]) === '')) {
                    continue; // Skip empty rows
                }
                
                // Parse date (Excel date is usually numeric)
                $fechaVal = $data[6] ?? null;
                $parsedFecha = null;
                
                if (is_numeric($fechaVal)) {
                    $parsedFecha = Carbon::instance(Date::excelToDateTimeObject($fechaVal))->format('Y-m-d');
                } else if (!empty($fechaVal)) {
                    try {
                        $parsedFecha = Carbon::parse($fechaVal)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $parsedFecha = null;
                    }
                }
                
                $mes = isset($data[4]) && (int)$data[4] > 0 ? (int)$data[4] : null;
                $año = isset($data[5]) && (int)$data[5] > 0 ? (int)$data[5] : null;
                
                if ($parsedFecha) {
                    $carbonDate = Carbon::parse($parsedFecha);
                    if (!$mes) $mes = $carbonDate->month;
                    if (!$año) $año = $carbonDate->year;
                }
                
                $parsedData[] = [
                    'nivel' => isset($data[0]) && $data[0] !== '' ? (int)$data[0] : null,
                    'tipo' => $data[1] ?? null,
                    'codigo' => $data[2] ?? null,
                    'cuenta' => $data[3] ?? null,
                    'mes' => $mes,
                    'año' => $año,
                    'fecha' => $parsedFecha,
                    'saldo' => isset($data[7]) && $data[7] !== '' ? (float)$data[7] : null,
                ];
            }
            
            $this->checkConflicts($parsedData);
            
        } catch (\Exception $e) {
            $this->reset('excelFile');
            $this->tempFilePath = null;
            session()->flash('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    private function checkConflicts($parsedData)
    {
        $this->conflictCount = 0;
        
        $existingRecords = FinancialHistory::select('año', 'mes', 'codigo')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->año . '-' . $item->mes . '-' . $item->codigo => true];
            })->toArray();
            
        foreach ($parsedData as $row) {
            $key = $row['año'] . '-' . $row['mes'] . '-' . $row['codigo'];
            if (isset($existingRecords[$key])) {
                $this->conflictCount++;
            }
        }
        
        if ($this->conflictCount > 0) {
            $this->showConflictWarning = true;
        } else {
            $this->confirmUpload(false);
        }
    }

    public function confirmUpload($replace = false)
    {
        if (!$this->tempFilePath || !file_exists($this->tempFilePath)) {
            $this->showConflictWarning = false;
            session()->flash('error', 'La sesión ha expirado o no se encontró el archivo. Por favor cárgalo de nuevo.');
            return;
        }
        
        try {
            $path = $this->tempFilePath;
            
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $highestRow = $worksheet->getHighestDataRow();
            $highestColumn = 'H';
            
            $rows = $worksheet->rangeToArray(
                'A1:' . $highestColumn . $highestRow,
                null,
                true,
                false,
                false
            );
            
            $parsedData = [];
            $headerSkipped = false;
            
            foreach ($rows as $data) {
                if (!$headerSkipped) {
                    $headerSkipped = true;
                    continue;
                }
                
                if ((!isset($data[2]) || trim((string)$data[2]) === '') && 
                    (!isset($data[3]) || trim((string)$data[3]) === '')) {
                    continue;
                }
                
                $fechaVal = $data[6] ?? null;
                $parsedFecha = null;
                if (is_numeric($fechaVal)) {
                    $parsedFecha = Carbon::instance(Date::excelToDateTimeObject($fechaVal))->format('Y-m-d');
                } else if (!empty($fechaVal)) {
                    try { $parsedFecha = Carbon::parse($fechaVal)->format('Y-m-d'); } catch (\Exception $e) {}
                }
                
                $mes = isset($data[4]) && (int)$data[4] > 0 ? (int)$data[4] : null;
                $año = isset($data[5]) && (int)$data[5] > 0 ? (int)$data[5] : null;
                
                if ($parsedFecha) {
                    $carbonDate = Carbon::parse($parsedFecha);
                    if (!$mes) $mes = $carbonDate->month;
                    if (!$año) $año = $carbonDate->year;
                }
                
                $parsedData[] = [
                    'nivel' => isset($data[0]) && $data[0] !== '' ? (int)$data[0] : null,
                    'tipo' => $data[1] ?? null,
                    'codigo' => $data[2] ?? null,
                    'cuenta' => $data[3] ?? null,
                    'mes' => $mes,
                    'año' => $año,
                    'fecha' => $parsedFecha,
                    'saldo' => isset($data[7]) && $data[7] !== '' ? (float)$data[7] : null,
                ];
            }
            
            \Illuminate\Support\Facades\DB::transaction(function () use ($parsedData, $replace) {
                if ($replace) {
                    $codesByPeriod = [];
                    foreach ($parsedData as $row) {
                        if (isset($row['año']) && isset($row['mes']) && isset($row['codigo'])) {
                            $codesByPeriod[$row['año'] . '-' . $row['mes']][] = $row['codigo'];
                        }
                    }
                    
                    foreach ($codesByPeriod as $period => $codes) {
                        [$year, $month] = explode('-', $period);
                        FinancialHistory::where('año', $year)
                                        ->where('mes', $month)
                                        ->whereIn('codigo', array_unique($codes))
                                        ->delete();
                    }
                }
                
                $now = now();
                $chunks = array_chunk($parsedData, 500);
                foreach ($chunks as $chunk) {
                    $insertData = array_map(function($row) use ($now) {
                        $row['created_at'] = $now;
                        $row['updated_at'] = $now;
                        return $row;
                    }, $chunk);
                    
                    FinancialHistory::insert($insertData);
                }
            });
            
            $inserted = count($parsedData);
            
            $this->reset('excelFile');
            $this->tempFilePath = null;
            $this->uploadId = null;
            $this->showConflictWarning = false;
            
            // Reload the selected period to the latest one
            $latestPeriod = FinancialHistory::select('año', 'mes')
                ->orderBy('año', 'desc')
                ->orderBy('mes', 'desc')
                ->first();
                
            if ($latestPeriod) {
                $this->selectedYear = $latestPeriod->año;
                $this->selectedMonth = $latestPeriod->mes;
            }
            
            session()->flash('message', "Se han procesado {$inserted} registros correctamente.");
            
        } catch (\Exception $e) {
            $this->reset('excelFile');
            session()->flash('error', 'Ocurrió un error al guardar: ' . $e->getMessage());
        }
    }

    public function cancelUpload()
    {
        $this->reset('excelFile');
        $this->uploadId = null;
        $this->showConflictWarning = false;
        session()->flash('message', 'Se canceló la carga del archivo.');
    }

    public function edit($id)
    {
        $record = FinancialHistory::findOrFail($id);
        $this->editId = $id;
        $this->nivel = $record->nivel;
        $this->tipo = $record->tipo;
        $this->codigo = $record->codigo;
        $this->cuenta = $record->cuenta;
        $this->mes = $record->mes;
        $this->año = $record->año;
        $this->fecha = $record->fecha;
        $this->saldo = $record->saldo;
        
        $this->isEditing = true;
        $this->showForm = true;
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $record = FinancialHistory::findOrFail($this->editId);
            $record->update([
                'nivel' => $this->nivel,
                'tipo' => $this->tipo,
                'codigo' => $this->codigo,
                'cuenta' => $this->cuenta,
                'mes' => $this->mes,
                'año' => $this->año,
                'fecha' => $this->fecha,
                'saldo' => $this->saldo,
            ]);
            session()->flash('message', 'Registro actualizado exitosamente.');
        } else {
            FinancialHistory::create([
                'nivel' => $this->nivel,
                'tipo' => $this->tipo,
                'codigo' => $this->codigo,
                'cuenta' => $this->cuenta,
                'mes' => $this->mes,
                'año' => $this->año,
                'fecha' => $this->fecha,
                'saldo' => $this->saldo,
            ]);
            session()->flash('message', 'Registro creado exitosamente.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        FinancialHistory::findOrFail($id)->delete();
        session()->flash('message', 'Registro eliminado.');
    }
    
    public function deleteAll()
    {
        FinancialHistory::truncate();
        session()->flash('message', 'Se han eliminado todos los registros.');
    }

    public function cancel()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['editId', 'nivel', 'tipo', 'codigo', 'cuenta', 'mes', 'año', 'fecha', 'saldo', 'isEditing']);
    }

    public function render()
    {
        $availableYears = FinancialHistory::select('año')
            ->distinct()
            ->orderBy('año', 'desc')
            ->pluck('año');
            
        $availableMonths = collect(range(1, 12));
        
        $availableTipos = FinancialHistory::select('tipo')
            ->whereNotNull('tipo')
            ->where('tipo', '!=', '')
            ->distinct()
            ->orderBy('tipo', 'asc')
            ->pluck('tipo');
            
        $query = FinancialHistory::query();
        
        if ($this->selectedYear) {
            $query->where('año', $this->selectedYear);
        }
        
        if ($this->selectedMonth && $this->selectedMonth !== 'all') {
            $query->where('mes', $this->selectedMonth);
        }
        
        if ($this->selectedTipo && $this->selectedTipo !== 'all') {
            $query->where('tipo', $this->selectedTipo);
        }

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('cuenta', 'like', '%' . $this->search . '%')
                  ->orWhere('codigo', 'like', '%' . $this->search . '%')
                  ->orWhere('tipo', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedMonth === 'all') {
            // Aggregate sum for the entire year
            $records = $query->selectRaw('nivel, tipo, codigo, cuenta, año, SUM(saldo) as saldo')
                             ->groupBy('nivel', 'tipo', 'codigo', 'cuenta', 'año')
                             ->orderBy('codigo', 'asc')
                             ->get();
                             
            // Set dummy ID so the loop doesn't fail, but we'll disable edits in the view
            $records->map(function($record, $index) {
                $record->id = 'sum_' . $index;
                $record->mes = 'all';
                return $record;
            });
        } else {
            // Must order by codigo to build the tree correctly
            $records = $query->orderBy('codigo', 'asc')->get();
        }
        
        // Find parents (a record is a parent if the next record's code starts with this record's code)
        $recordsArray = $records->toArray();
        $parentCodes = [];
        for ($i = 0; $i < count($recordsArray) - 1; $i++) {
            $currentCode = (string)$recordsArray[$i]['codigo'];
            $nextCode = (string)$recordsArray[$i+1]['codigo'];
            if ($currentCode !== '' && str_starts_with($nextCode, $currentCode)) {
                $parentCodes[$currentCode] = true;
            }
        }
        
        // Enrich records with is_parent flag
        $records->map(function($record) use ($parentCodes) {
            $record->is_parent = isset($parentCodes[(string)$record->codigo]);
            return $record;
        });

        return view('livewire.historical-financials', [
            'records' => $records,
            'availableYears' => $availableYears,
            'availableMonths' => $availableMonths,
            'availableTipos' => $availableTipos,
        ])->layout('layouts.ledger');
    }
}
