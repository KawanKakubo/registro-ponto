<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use Illuminate\Http\Request;

class EstablishmentController extends Controller
{
    public function index()
    {
        $establishments = Establishment::orderBy('corporate_name')->get();
        return view('establishments.index', compact('establishments'));
    }

    public function create()
    {
        return view('establishments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'corporate_name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'cnpj' => 'required|string|max:18|unique:establishments',
            'state_registration' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);

        Establishment::create($validated);

        return redirect()->route('establishments.index')
            ->with('success', 'Estabelecimento criado com sucesso!');
    }

    public function show(Establishment $establishment)
    {
        return view('establishments.show', compact('establishment'));
    }

    public function edit(Establishment $establishment)
    {
        return view('establishments.edit', compact('establishment'));
    }

    public function update(Request $request, Establishment $establishment)
    {
        $validated = $request->validate([
            'corporate_name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'cnpj' => 'required|string|max:18|unique:establishments,cnpj,' . $establishment->id,
            'state_registration' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);

        $establishment->update($validated);

        return redirect()->route('establishments.index')
            ->with('success', 'Estabelecimento atualizado com sucesso!');
    }

    public function destroy(Establishment $establishment)
    {
        $establishment->delete();

        return redirect()->route('establishments.index')
            ->with('success', 'Estabelecimento excluído com sucesso!');
    }
}
