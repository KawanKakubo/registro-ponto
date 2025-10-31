<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Establishment;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = User::where('role', 'admin')
            ->with('establishment')
            ->orderBy('name')
            ->paginate(15);

        return view('admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $establishments = Establishment::orderBy('corporate_name')->get();
        return view('admins.create', compact('establishments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Remove qualquer formatação do CPF primeiro
        $cpfLimpo = preg_replace('/[^0-9]/', '', $request->cpf);

        $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'establishment_id' => 'nullable|exists:establishments,id',
        ], [
            'name.required' => 'O nome é obrigatório',
            'cpf.required' => 'O CPF é obrigatório',
            'email.required' => 'O email é obrigatório',
            'email.unique' => 'Este email já está cadastrado',
            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres',
            'password.confirmed' => 'As senhas não coincidem',
        ]);

        // Valida se o CPF tem 11 dígitos
        if (strlen($cpfLimpo) !== 11) {
            return back()->withErrors([
                'cpf' => 'O CPF deve conter exatamente 11 dígitos.',
            ])->withInput();
        }

        // Verifica se o CPF já está cadastrado
        if (User::where('cpf', $cpfLimpo)->exists()) {
            return back()->withErrors([
                'cpf' => 'Este CPF já está cadastrado.',
            ])->withInput();
        }

        $cpf = $cpfLimpo;

        User::create([
            'name' => $request->name,
            'cpf' => $cpf,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'is_active' => true,
            'establishment_id' => $request->establishment_id,
        ]);

        return redirect()->route('admins.index')->with('success', 'Administrador cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $admin)
    {
        return view('admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $admin)
    {
        $establishments = Establishment::orderBy('corporate_name')->get();
        return view('admins.edit', compact('admin', 'establishments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $admin)
    {
        // Remove qualquer formatação do CPF primeiro
        $cpfLimpo = preg_replace('/[^0-9]/', '', $request->cpf);

        $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'password' => 'nullable|string|min:6|confirmed',
            'establishment_id' => 'nullable|exists:establishments,id',
            'is_active' => 'boolean',
        ]);

        // Valida se o CPF tem 11 dígitos
        if (strlen($cpfLimpo) !== 11) {
            return back()->withErrors([
                'cpf' => 'O CPF deve conter exatamente 11 dígitos.',
            ])->withInput();
        }

        // Verifica se o CPF já está cadastrado em outro usuário
        if (User::where('cpf', $cpfLimpo)->where('id', '!=', $admin->id)->exists()) {
            return back()->withErrors([
                'cpf' => 'Este CPF já está cadastrado.',
            ])->withInput();
        }

        $cpf = $cpfLimpo;

        $data = [
            'name' => $request->name,
            'cpf' => $cpf,
            'email' => $request->email,
            'is_active' => $request->has('is_active'),
            'establishment_id' => $request->establishment_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return redirect()->route('admins.index')->with('success', 'Administrador atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $admin)
    {
        $admin->delete();
        return redirect()->route('admins.index')->with('success', 'Administrador removido com sucesso!');
    }
}
