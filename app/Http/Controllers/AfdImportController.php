<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAfdImport;
use App\Models\AfdImport;
use App\Services\AfdParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AfdImportController extends Controller
{
    public function index()
    {
        $imports = AfdImport::orderBy('created_at', 'desc')->get();
        return view('afd-imports.index', compact('imports'));
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
}
