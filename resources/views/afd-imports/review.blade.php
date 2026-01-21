@extends('layouts.main')

@section('content')
<div class="mb-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center flex-1">
            <a href="{{ route('afd-imports.show', $afdImport) }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-user-plus text-yellow-600 mr-3"></i>Revisão de Colaboradores
                    <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>{{ $afdImport->pending_count }} Pendente(s)
                    </span>
                </h1>
                <p class="text-gray-600 mt-2">{{ $afdImport->file_name }}</p>
            </div>
        </div>
        
        <form action="{{ route('afd-imports.skip-all', $afdImport) }}" method="POST" class="inline"
              onsubmit="return confirm('Tem certeza que deseja ignorar todos os {{ $afdImport->pending_count }} colaboradores pendentes? Os registros de ponto deles NÃO serão importados.');">
            @csrf
            <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg transition">
                <i class="fas fa-forward mr-2"></i>Ignorar Todos
            </button>
        </form>
    </div>

    <!-- Info Card -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-yellow-600 text-2xl mr-4 mt-1"></i>
            <div>
                <h3 class="font-bold text-yellow-900 mb-2">Colaboradores Não Encontrados</h3>
                <p class="text-sm text-yellow-800">
                    Os colaboradores abaixo possuem registros de ponto no arquivo AFD, mas não foram encontrados no sistema.
                    Você pode <strong>cadastrá-los</strong> preenchendo as informações faltantes, ou <strong>ignorar</strong> para pular.
                </p>
            </div>
        </div>
    </div>

    <!-- Lista de Colaboradores Pendentes -->
    <div class="space-y-6">
        @foreach($pendingEmployees as $employee)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden" x-data="{ expanded: false }">
            <!-- Header do Card -->
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-4 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-yellow-700 rounded-full p-3 mr-4">
                            <i class="fas fa-user text-2xl text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">
                                @if($employee['matricula'])
                                    Matrícula: {{ $employee['matricula'] }}
                                @elseif($employee['pis'])
                                    PIS: {{ $employee['pis'] }}
                                @elseif($employee['cpf'])
                                    CPF: {{ preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $employee['cpf']) }}
                                @else
                                    Identificador: {{ $employee['key'] }}
                                @endif
                            </h3>
                            <p class="text-yellow-100 text-sm">
                                <i class="fas fa-clock mr-1"></i>{{ $employee['records_count'] }} registro(s) de ponto
                                &bull;
                                <i class="fas fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($employee['first_record'])->format('d/m/Y') }}
                                @if($employee['first_record'] !== $employee['last_record'])
                                    até {{ \Carbon\Carbon::parse($employee['last_record'])->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="expanded = !expanded" 
                                class="bg-white hover:bg-gray-100 text-yellow-700 font-semibold px-4 py-2 rounded-lg transition shadow">
                            <i class="fas fa-chevron-down" x-show="!expanded"></i>
                            <i class="fas fa-chevron-up" x-show="expanded" style="display: none;"></i>
                            <span x-show="!expanded">Cadastrar</span>
                            <span x-show="expanded" style="display: none;">Fechar</span>
                        </button>
                        <form action="{{ route('afd-imports.skip-employee', [$afdImport, $employee['key']]) }}" method="POST" class="inline"
                              onsubmit="return confirm('Ignorar este colaborador? Os {{ $employee['records_count'] }} registros de ponto NÃO serão importados.');">
                            @csrf
                            <button type="submit" class="bg-red-600 bg-opacity-80 hover:bg-opacity-100 text-white font-semibold px-4 py-2 rounded-lg transition">
                                <i class="fas fa-times mr-1"></i>Ignorar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Formulário de Cadastro (Expandível) -->
            <div x-show="expanded" x-collapse class="p-6 bg-gray-50">
                <form action="{{ route('afd-imports.register-employee', [$afdImport, $employee['key']]) }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nome Completo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-1"></i>Nome Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="full_name" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                   placeholder="Digite o nome completo do colaborador">
                        </div>

                        <!-- Matrícula -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-hashtag mr-1"></i>Matrícula <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="matricula" required
                                   value="{{ $employee['matricula'] ?? '' }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 {{ isset($employee['matricula']) && $employee['matricula'] ? 'bg-gray-100' : '' }}"
                                   placeholder="Digite a matrícula"
                                   {{ isset($employee['matricula']) && $employee['matricula'] ? 'readonly' : '' }}>
                            @if(isset($employee['matricula']) && $employee['matricula'])
                                <p class="text-xs text-gray-500 mt-1">Matrícula detectada no arquivo AFD</p>
                            @endif
                        </div>

                        <!-- CPF -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-id-card mr-1"></i>CPF
                            </label>
                            <input type="text" name="cpf" 
                                   value="{{ $employee['cpf'] ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $employee['cpf']) : '' }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                   placeholder="000.000.000-00"
                                   x-mask="999.999.999-99">
                        </div>

                        <!-- PIS/PASEP -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-fingerprint mr-1"></i>PIS/PASEP
                            </label>
                            <input type="text" name="pis_pasep" 
                                   value="{{ $employee['pis'] ?? '' }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 {{ $employee['pis'] ? 'bg-gray-100' : '' }}"
                                   placeholder="00000000000"
                                   {{ $employee['pis'] ? 'readonly' : '' }}>
                            @if($employee['pis'])
                                <p class="text-xs text-gray-500 mt-1">PIS detectado no arquivo AFD</p>
                            @endif
                        </div>

                        <!-- Estabelecimento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building mr-1"></i>Estabelecimento <span class="text-red-500">*</span>
                            </label>
                            <select name="establishment_id" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                <option value="">Selecione...</option>
                                @foreach($establishments as $establishment)
                                    <option value="{{ $establishment->id }}">{{ $establishment->corporate_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Departamento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-sitemap mr-1"></i>Departamento
                            </label>
                            <select name="department_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                <option value="">Selecione...</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Data de Admissão -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i>Data de Admissão
                            </label>
                            <input type="date" name="admission_date"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>

                        <!-- Cargo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-briefcase mr-1"></i>Cargo/Função
                            </label>
                            <input type="text" name="position"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                   placeholder="Ex: Analista, Técnico, etc.">
                        </div>
                    </div>

                    <!-- Info da Matrícula -->
                    @if($employee['matricula'])
                    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Matrícula:</strong> {{ $employee['matricula'] }} (será vinculada automaticamente)
                        </p>
                    </div>
                    @endif

                    <!-- Botões -->
                    <div class="mt-6 flex items-center justify-end space-x-4">
                        <button type="button" @click="expanded = false"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition shadow-lg">
                            <i class="fas fa-check mr-2"></i>Cadastrar e Importar {{ $employee['records_count'] }} Registro(s)
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Ações Finais -->
    <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-900">Ações em Lote</h3>
                <p class="text-gray-600 text-sm">Após resolver todos os pendentes, a importação será marcada como concluída.</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('afd-imports.show', $afdImport) }}" 
                   class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
                <form action="{{ route('afd-imports.skip-all', $afdImport) }}" method="POST" class="inline"
                      onsubmit="return confirm('Tem certeza que deseja ignorar todos os {{ $afdImport->pending_count }} colaboradores pendentes?');">
                    @csrf
                    <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition">
                        <i class="fas fa-forward mr-2"></i>Ignorar Todos e Finalizar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
