<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\TimesheetGeneratorService;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function index()
    {
        $departments = \App\Models\Department::with('establishment')
            ->orderBy('name')
            ->get();
        
        return view('timesheets.index', compact('departments'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], [
            'department_id.required' => 'Por favor, selecione um departamento.',
            'employee_ids.required' => 'Por favor, selecione pelo menos um colaborador.',
            'employee_ids.min' => 'Por favor, selecione pelo menos um colaborador.',
            'start_date.required' => 'Por favor, informe a data inicial.',
            'end_date.required' => 'Por favor, informe a data final.',
            'end_date.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ]);

        // Se apenas um colaborador, redireciona direto para a visualização
        if (count($request->employee_ids) === 1) {
            return redirect()->route('timesheets.show', [
                'employee_id' => $request->employee_ids[0],
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);
        }

        // Múltiplos colaboradores: gerar PDF zip ou listagem
        return redirect()->route('timesheets.show', [
            'employee_ids' => implode(',', $request->employee_ids),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
    }

    public function show(Request $request)
    {
        // Caso 1: Um único colaborador (visualização individual)
        if ($request->has('employee_id') && !$request->has('employee_ids')) {
            $employee = Employee::with(['establishment', 'department'])->findOrFail($request->employee_id);
            
            $generator = new TimesheetGeneratorService();
            $data = $generator->generate($employee, $request->start_date, $request->end_date);

            return view('timesheets.show', $data);
        }

        // Caso 2: Múltiplos colaboradores (listagem para download)
        $employeeIds = $request->input('employee_ids');
        
        // Se veio como string separada por vírgula, converte para array
        if (is_string($employeeIds)) {
            $employeeIds = explode(',', $employeeIds);
        }

        // Busca os colaboradores
        $employees = Employee::with(['establishment', 'department'])
            ->whereIn('id', $employeeIds)
            ->orderBy('full_name')
            ->get();

        if ($employees->isEmpty()) {
            abort(404, 'Nenhum colaborador encontrado.');
        }

        return view('timesheets.multiple', [
            'employees' => $employees,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
    }

    public function downloadZip(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $employeeIds = $request->input('employee_ids');
        
        // Busca os colaboradores
        $employees = Employee::with(['establishment', 'department'])
            ->whereIn('id', $employeeIds)
            ->orderBy('full_name')
            ->get();

        if ($employees->isEmpty()) {
            return back()->with('error', 'Nenhum colaborador encontrado.');
        }

        // Cria um arquivo ZIP temporário
        $zipFileName = 'cartoes_ponto_' . now()->format('Y-m-d_His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Garante que o diretório temp existe
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Não foi possível criar o arquivo ZIP.');
        }

        $generator = new TimesheetGeneratorService();
        
        // Gera um PDF para cada colaborador
        foreach ($employees as $employee) {
            try {
                $data = $generator->generate($employee, $request->start_date, $request->end_date);
                
                // Renderiza a view como HTML
                $html = view('timesheets.pdf', $data)->render();
                
                // Converte HTML para PDF usando DomPDF com otimizações
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                $pdf->setPaper('A4', 'portrait'); // Modo retrato (vertical)
                $pdf->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'Arial',
                    'dpi' => 96,
                    'enable_php' => true,
                    'enable_css_float' => true,
                ]);
                
                // Adiciona o PDF ao ZIP
                $fileName = $this->sanitizeFileName($employee->full_name) . '_' . 
                           str_replace('-', '', $request->start_date) . '_' . 
                           str_replace('-', '', $request->end_date) . '.pdf';
                
                $zip->addFromString($fileName, $pdf->output());
            } catch (\Exception $e) {
                // Log do erro mas continua com os próximos
                \Log::error("Erro ao gerar PDF para {$employee->full_name}: {$e->getMessage()}");
            }
        }

        $zip->close();

        // Download do arquivo
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Remove caracteres especiais do nome do arquivo
     */
    private function sanitizeFileName(string $name): string
    {
        // Remove acentos
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        // Remove caracteres especiais
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
        // Remove underscores duplicados
        $name = preg_replace('/_+/', '_', $name);
        // Remove underscores no início e fim
        return trim($name, '_');
    }
}
