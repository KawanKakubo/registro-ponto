<?php

namespace App\Http\Controllers;

use App\Jobs\ImportEmployeesFromCsv;
use App\Models\EmployeeImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class EmployeeImportController extends Controller
{
    public function index()
    {
        $imports = EmployeeImport::orderBy('created_at', 'desc')->paginate(20);
        return view('employee-imports.index', compact('imports'));
    }

    public function create()
    {
        return view('employee-imports.create');
    }

    /**
     * Download do template CSV
     */
    public function downloadTemplate()
    {
        $headers = [
            'cpf',
            'full_name',
            'pis_pasep',
            'establishment_id',
            'department_id',
            'admission_date',
            'role'
        ];

        $example = [
            '123.456.789-00',
            'João da Silva',
            '123.45678.90-1',
            '1',
            '1',
            '2025-01-15',
            'Analista'
        ];

        $csv = implode(',', $headers) . "\n" . implode(',', $example);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="modelo_colaboradores.csv"',
        ]);
    }

    /**
     * Upload e validação inicial do arquivo
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        try {
            $file = $request->file('csv_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $fileSize = $file->getSize();
            
            $directory = storage_path('app/employee-imports');
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            
            $fullPath = $directory . '/' . $fileName;
            $file->move($directory, $fileName);
            
            if (!file_exists($fullPath)) {
                return redirect()->route('employee-imports.create')
                    ->with('error', 'Erro ao salvar o arquivo.');
            }

            // Validação prévia do arquivo
            $preview = $this->previewImport($fullPath);

            // Criar registro de importação
            $import = EmployeeImport::create([
                'file_path' => 'employee-imports/' . $fileName,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $fileSize,
                'status' => 'pending',
                'total_rows' => $preview['total_rows'],
            ]);

            return view('employee-imports.preview', [
                'import' => $import,
                'preview' => $preview
            ]);

        } catch (\Exception $e) {
            return redirect()->route('employee-imports.create')
                ->with('error', 'Erro durante o upload: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar e processar importação
     */
    public function process(EmployeeImport $import)
    {
        if ($import->status !== 'pending') {
            return redirect()->route('employee-imports.show', $import)
                ->with('error', 'Esta importação já foi processada.');
        }

        // Despachar job para processamento
        ImportEmployeesFromCsv::dispatch($import);

        return redirect()->route('employee-imports.show', $import)
            ->with('success', 'Importação iniciada! O processamento está sendo realizado em segundo plano.');
    }

    public function show(EmployeeImport $import)
    {
        return view('employee-imports.show', compact('import'));
    }

    /**
     * Pré-visualização dos dados do CSV
     */
    protected function previewImport(string $filePath): array
    {
        $preview = [
            'total_rows' => 0,
            'valid_rows' => 0,
            'invalid_rows' => 0,
            'new_employees' => 0,
            'existing_employees' => 0,
            'errors' => [],
            'sample_data' => []
        ];

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle, 1000, ',');
        
        $lineNumber = 1;
        $sampleCount = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false && $sampleCount < 5) {
            $lineNumber++;
            $preview['total_rows']++;

            try {
                $data = array_combine($header, $row);
                
                $validator = Validator::make($data, [
                    'cpf' => 'required|string|size:14',
                    'full_name' => 'required|string|max:255',
                    'pis_pasep' => 'required|string|size:14',
                    'establishment_id' => 'required|exists:establishments,id',
                    'department_id' => 'required|exists:departments,id',
                    'admission_date' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $preview['invalid_rows']++;
                    if (count($preview['errors']) < 10) {
                        $preview['errors'][] = [
                            'line' => $lineNumber,
                            'errors' => $validator->errors()->all()
                        ];
                    }
                } else {
                    $preview['valid_rows']++;
                    
                    // Verificar se colaborador já existe
                    $exists = \App\Models\Employee::where('cpf', $data['cpf'])->exists();
                    if ($exists) {
                        $preview['existing_employees']++;
                    } else {
                        $preview['new_employees']++;
                    }

                    if ($sampleCount < 5) {
                        $preview['sample_data'][] = $data;
                        $sampleCount++;
                    }
                }

            } catch (\Exception $e) {
                $preview['invalid_rows']++;
            }
        }

        // Contar o resto das linhas
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $preview['total_rows']++;
        }

        fclose($handle);

        return $preview;
    }
}
