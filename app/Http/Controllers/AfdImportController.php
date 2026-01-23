<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAfdImport;
use App\Models\AfdImport;
use App\Models\Person;
use App\Models\EmployeeRegistration;
use App\Models\TimeRecord;
use App\Models\Establishment;
use App\Models\Department;
use App\Services\AfdParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AfdImportController extends Controller
{
    public function index()
    {
        $imports = AfdImport::with('importedByUser')->orderBy('created_at', 'desc')->get();
        
        $stats = [
            'total' => $imports->count(),
            'completed' => $imports->where('status', 'completed')->count(),
            'pending' => $imports->where('status', 'pending')->count(),
            'pending_review' => $imports->where('status', 'pending_review')->count(),
            'failed' => $imports->where('status', 'failed')->count(),
        ];
        
        return view('afd-imports.index', compact('imports', 'stats'));
    }

    public function create()
    {
        return view('afd-imports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'afd_file' => 'required|file|mimes:txt|max:10240',
        ]);

        try {
            $file = $request->file('afd_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Obtém o tamanho ANTES de mover o arquivo
            $fileSize = $file->getSize();
            
            // Define o caminho direto
            $directory = storage_path('app/afd-files');
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            
            // Move o arquivo diretamente
            $fullPath = $directory . '/' . $fileName;
            $file->move($directory, $fileName);
            
            // Verifica se o arquivo foi realmente salvo
            if (!file_exists($fullPath)) {
                return redirect()->route('afd-imports.create')
                    ->with('error', 'Erro ao salvar o arquivo. Verifique as permissões do diretório storage/app/afd-files');
            }
            
            $filePath = 'afd-files/' . $fileName;

            $afdImport = AfdImport::create([
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'status' => 'processing',
                'imported_by' => auth()->id(),
                'imported_at' => now(),
            ]);

            // Despachar job para processamento assíncrono
            ProcessAfdImport::dispatch($afdImport);

            return redirect()->route('afd-imports.show', $afdImport)
                ->with('success', 'Arquivo enviado com sucesso! A importação está sendo processada em segundo plano.');
        } catch (\Exception $e) {
            return redirect()->route('afd-imports.create')
                ->with('error', 'Erro durante o upload: ' . $e->getMessage());
        }
    }

    public function show(AfdImport $afdImport)
    {
        return view('afd-imports.show', compact('afdImport'));
    }

    /**
     * Exibe a tela de revisão de colaboradores pendentes
     */
    public function review(AfdImport $afdImport)
    {
        if (!$afdImport->hasPendingEmployees()) {
            return redirect()->route('afd-imports.show', $afdImport)
                ->with('info', 'Esta importação não possui colaboradores pendentes.');
        }

        $pendingEmployees = $afdImport->getPendingEmployeesCollection();
        $establishments = Establishment::orderBy('corporate_name')->get();
        $departments = Department::orderBy('name')->get();

        return view('afd-imports.review', compact('afdImport', 'pendingEmployees', 'establishments', 'departments'));
    }

    /**
     * Cadastra um colaborador pendente
     */
    public function registerEmployee(Request $request, AfdImport $afdImport, string $employeeKey)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'matricula' => 'required|string|max:20|unique:employee_registrations,matricula',
            'cpf' => 'nullable|string|max:14',
            'pis_pasep' => 'nullable|string|max:15',
            'establishment_id' => 'required|exists:establishments,id',
            'department_id' => 'nullable|exists:departments,id',
            'admission_date' => 'nullable|date',
            'position' => 'nullable|string|max:255',
        ], [
            'matricula.required' => 'A matrícula é obrigatória.',
            'matricula.unique' => 'Esta matrícula já está em uso por outro colaborador.',
        ]);

        try {
            DB::beginTransaction();

            // Busca os dados do colaborador pendente
            $pendingEmployees = $afdImport->pending_employees ?? [];
            $employeeData = collect($pendingEmployees)->firstWhere('key', $employeeKey);

            if (!$employeeData) {
                return back()->with('error', 'Colaborador pendente não encontrado.');
            }

            // Cria ou busca a pessoa
            $cpf = $request->cpf ? preg_replace('/[^0-9]/', '', $request->cpf) : null;
            $pis = $request->pis_pasep ?: ($employeeData['pis'] ?? null);
            
            // Tenta buscar pessoa existente por CPF ou PIS
            $person = null;
            if ($cpf) {
                $person = Person::where('cpf', $cpf)->first();
            }
            if (!$person && $pis) {
                $person = Person::where('pis_pasep', preg_replace('/[^0-9]/', '', $pis))->first();
            }

            if (!$person) {
                $person = Person::create([
                    'full_name' => $request->full_name,
                    'cpf' => $cpf,
                    'pis_pasep' => $pis,
                ]);
            }

            // Cria o vínculo (matrícula)
            $registration = EmployeeRegistration::create([
                'person_id' => $person->id,
                'matricula' => $request->matricula,
                'establishment_id' => $request->establishment_id,
                'department_id' => $request->department_id,
                'admission_date' => $request->admission_date,
                'position' => $request->position,
                'status' => 'active',
            ]);

            // Processa os registros de ponto pendentes deste colaborador
            $pendingRecords = $afdImport->getPendingRecordsFor($employeeKey);
            $importedCount = 0;

            foreach ($pendingRecords as $record) {
                // Verifica se já existe
                $recordedAt = \Carbon\Carbon::parse($record['recorded_at']);
                
                $exists = TimeRecord::where('employee_registration_id', $registration->id)
                    ->where('recorded_at', $recordedAt)
                    ->exists();

                if (!$exists) {
                    TimeRecord::create([
                        'employee_registration_id' => $registration->id,
                        'recorded_at' => $recordedAt,
                        'record_date' => $record['record_date'],
                        'record_time' => $record['record_time'],
                        'nsr' => $record['nsr'],
                        'record_type' => $record['record_type'],
                        'imported_from_afd' => true,
                        'afd_file_name' => $afdImport->file_name,
                    ]);
                    $importedCount++;
                }
            }

            // Atualiza o total de registros
            $afdImport->increment('total_records', $importedCount);

            // Remove o colaborador da lista de pendentes
            $afdImport->removePendingEmployee($employeeKey);

            DB::commit();

            $remainingCount = count($afdImport->fresh()->pending_employees ?? []);
            
            if ($remainingCount > 0) {
                return redirect()->route('afd-imports.review', $afdImport)
                    ->with('success', "Colaborador {$request->full_name} cadastrado com sucesso! {$importedCount} registros de ponto importados. Restam {$remainingCount} colaborador(es) pendente(s).");
            }

            return redirect()->route('afd-imports.show', $afdImport)
                ->with('success', "Colaborador {$request->full_name} cadastrado com sucesso! {$importedCount} registros de ponto importados. Todos os pendentes foram resolvidos!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao cadastrar colaborador: ' . $e->getMessage());
        }
    }

    /**
     * Pula um colaborador pendente (ignora)
     */
    public function skipEmployee(AfdImport $afdImport, string $employeeKey)
    {
        try {
            $pendingEmployees = $afdImport->pending_employees ?? [];
            $employeeData = collect($pendingEmployees)->firstWhere('key', $employeeKey);

            if (!$employeeData) {
                return back()->with('error', 'Colaborador pendente não encontrado.');
            }

            // Remove o colaborador da lista de pendentes
            $afdImport->removePendingEmployee($employeeKey);

            $remainingCount = count($afdImport->fresh()->pending_employees ?? []);
            
            if ($remainingCount > 0) {
                return redirect()->route('afd-imports.review', $afdImport)
                    ->with('info', "Colaborador ignorado. Restam {$remainingCount} colaborador(es) pendente(s).");
            }

            return redirect()->route('afd-imports.show', $afdImport)
                ->with('info', 'Colaborador ignorado. Todos os pendentes foram resolvidos!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao ignorar colaborador: ' . $e->getMessage());
        }
    }

    /**
     * Pula todos os colaboradores pendentes
     */
    public function skipAll(AfdImport $afdImport)
    {
        try {
            $count = $afdImport->pending_count;

            $afdImport->update([
                'pending_employees' => null,
                'pending_records' => null,
                'pending_count' => 0,
                'status' => 'completed',
            ]);

            return redirect()->route('afd-imports.show', $afdImport)
                ->with('info', "{$count} colaborador(es) pendente(s) foram ignorados. Importação finalizada.");

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao ignorar colaboradores: ' . $e->getMessage());
        }
    }
}
