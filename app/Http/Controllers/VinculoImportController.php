<?php

namespace App\Http\Controllers;

use App\Models\VinculoImport;
use App\Jobs\ImportVinculosJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VinculoImportController extends Controller
{
    /**
     * Exibe a tela de upload de vínculos
     */
    public function index()
    {
        $imports = VinculoImport::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('vinculo-imports.index', compact('imports'));
    }

    /**
     * Exibe o formulário de upload
     */
    public function create()
    {
        return view('vinculo-imports.upload');
    }

    /**
     * Processa o upload e inicia a importação
     */
    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('csv_file');
            $filename = $file->getClientOriginalName();
            $timestamp = time();
            
            // Salvar arquivo no storage
            $csvPath = $file->storeAs('vinculo-imports', "{$timestamp}_{$filename}");

            // Pré-validar CSV (verificar header)
            $validation = $this->validateCsvStructure(Storage::path($csvPath));
            
            if (!$validation['valid']) {
                // Remover arquivo se inválido
                Storage::delete($csvPath);
                
                return back()->with('error', '❌ Arquivo CSV inválido: ' . $validation['message']);
            }

            // Criar registro de importação
            $import = VinculoImport::create([
                'filename' => $filename,
                'csv_path' => $csvPath,
                'user_id' => auth()->id(),
                'total_linhas' => $validation['line_count'],
                'status' => 'pending',
            ]);

            // Despachar job para fila
            ImportVinculosJob::dispatch($csvPath, $import->id, auth()->id());

            // Atualizar status
            $import->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            return redirect()->route('vinculo-imports.show', $import)
                ->with('success', "✅ Importação iniciada! Processando {$validation['line_count']} linhas...");

        } catch (\Exception $e) {
            Log::error('Erro ao iniciar importação de vínculos: ' . $e->getMessage());
            
            return back()->with('error', '❌ Erro ao processar arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Exibe os detalhes e resultados de uma importação
     */
    public function show(VinculoImport $import)
    {
        $import->load('user');

        // Tentar carregar resultados e erros
        $resultsPath = "vinculo-imports/results-{$import->id}.json";
        $errorsPath = "vinculo-imports/errors-{$import->id}.json";

        $results = null;
        $errors = null;

        if (Storage::exists($resultsPath)) {
            $results = json_decode(Storage::get($resultsPath), true);
            
            // Atualizar registro com resultados
            $import->update([
                'pessoas_criadas' => $results['pessoas_criadas'] ?? 0,
                'pessoas_atualizadas' => $results['pessoas_atualizadas'] ?? 0,
                'vinculos_criados' => $results['vinculos_criados'] ?? 0,
                'vinculos_atualizados' => $results['vinculos_atualizados'] ?? 0,
                'jornadas_associadas' => $results['jornadas_associadas'] ?? 0,
                'erros' => count($results['erros'] ?? []),
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        if (Storage::exists($errorsPath)) {
            $errors = json_decode(Storage::get($errorsPath), true);
        }

        return view('vinculo-imports.show', compact('import', 'results', 'errors'));
    }

    /**
     * Exibe a página de erros detalhados
     */
    public function showErrors(VinculoImport $import)
    {
        $import->load('user');

        // Carregar CSV original
        $csvPath = Storage::path($import->csv_path);
        $csvData = [];
        
        if (file_exists($csvPath)) {
            $handle = fopen($csvPath, 'r');
            $header = fgetcsv($handle);
            $lineNumber = 1;
            
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;
                $csvData[$lineNumber] = array_combine($header, $row);
            }
            
            fclose($handle);
        }

        // Carregar erros
        $errorsPath = "vinculo-imports/errors-{$import->id}.json";
        $errors = [];
        
        if (Storage::exists($errorsPath)) {
            $errorsData = json_decode(Storage::get($errorsPath), true);
            
            // Combinar erros com dados do CSV
            foreach ($errorsData as $error) {
                $lineNum = $error['line'];
                $errors[] = [
                    'line' => $lineNum,
                    'data' => $csvData[$lineNum] ?? $error['data'],
                    'errors' => $error['errors'],
                ];
            }
        }

        return view('vinculo-imports.errors', compact('import', 'errors'));
    }

    /**
     * Valida a estrutura do CSV
     */
    protected function validateCsvStructure(string $filePath): array
    {
        try {
            $handle = fopen($filePath, 'r');
            
            if ($handle === false) {
                return [
                    'valid' => false,
                    'message' => 'Não foi possível abrir o arquivo',
                ];
            }

            // Ler header
            $header = fgetcsv($handle);
            
            if ($header === false) {
                fclose($handle);
                return [
                    'valid' => false,
                    'message' => 'Arquivo vazio ou formato inválido',
                ];
            }

            // Validar colunas obrigatórias
            $requiredColumns = ['NOME', 'Nº PIS/PASEP', 'Nº IDENTIFICADOR', 'HORÁRIO'];
            $missingColumns = array_diff($requiredColumns, $header);
            
            if (!empty($missingColumns)) {
                fclose($handle);
                return [
                    'valid' => false,
                    'message' => 'Colunas obrigatórias faltando: ' . implode(', ', $missingColumns),
                ];
            }

            // Contar linhas
            $lineCount = 0;
            while (fgetcsv($handle) !== false) {
                $lineCount++;
            }

            fclose($handle);

            return [
                'valid' => true,
                'line_count' => $lineCount,
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Erro ao validar CSV: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Faz download do arquivo CSV original
     */
    public function download(VinculoImport $import)
    {
        if (!Storage::exists($import->csv_path)) {
            return back()->with('error', '❌ Arquivo CSV não encontrado');
        }

        return Storage::download($import->csv_path, $import->filename);
    }

    /**
     * Faz download do relatório de erros em CSV
     */
    public function downloadErrors(VinculoImport $import)
    {
        $errorsPath = "vinculo-imports/errors-{$import->id}.json";
        
        if (!Storage::exists($errorsPath)) {
            return back()->with('error', '❌ Nenhum erro encontrado para esta importação');
        }

        $errors = json_decode(Storage::get($errorsPath), true);

        // Criar CSV de erros
        $csvContent = "Linha,Erro,Dados\n";
        
        foreach ($errors as $error) {
            $linha = $error['line'];
            $erroMsg = implode('; ', $error['errors']);
            $dados = json_encode($error['data']);
            
            $csvContent .= "{$linha},\"{$erroMsg}\",\"{$dados}\"\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=erros-importacao-{$import->id}.csv");
    }
}
