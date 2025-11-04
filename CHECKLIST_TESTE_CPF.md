# ✅ CHECKLIST: Teste de Correção CPF

**Data**: 04/11/2025  
**Erro Corrigido**: `validation.size.string`  
**Controller**: `EmployeeController`

---

## 📋 TESTES OBRIGATÓRIOS

### 1️⃣ CADASTRO DE PESSOA (sem vínculo)

**URL**: http://127.0.0.1:8000/employees/create

- [ ] **Teste 1.1**: CPF com máscara completa
  - Nome: João da Silva
  - CPF: `123.456.789-00`
  - **Esperado**: ✅ Criar pessoa com sucesso

- [ ] **Teste 1.2**: CPF sem formatação
  - Nome: Maria Oliveira
  - CPF: `98765432100`
  - **Esperado**: ✅ Criar pessoa com sucesso

- [ ] **Teste 1.3**: CPF parcialmente formatado
  - Nome: Pedro Santos
  - CPF: `111.222.33344`
  - **Esperado**: ❌ Erro de validação (11 dígitos após limpeza, mas inválido)

- [ ] **Teste 1.4**: CPF duplicado
  - Nome: Ana Costa
  - CPF: `123.456.789-00` (já existe)
  - **Esperado**: ❌ Erro: "O cpf já está em uso."

---

### 2️⃣ CADASTRO DE PESSOA + PRIMEIRO VÍNCULO

**URL**: http://127.0.0.1:8000/employees/create

- [ ] **Teste 2.1**: Criar pessoa com vínculo completo
  - ✅ Marcar "Criar primeiro vínculo"
  - Nome: Carlos Alberto
  - CPF: `222.333.444-55`
  - PIS/PASEP: `123.45678.90-1`
  - Matrícula: `001`
  - Data Admissão: `01/11/2025`
  - Estabelecimento: Selecionar um
  - Cargo: Auxiliar Administrativo
  - **Esperado**: ✅ Criar pessoa + vínculo com sucesso

- [ ] **Teste 2.2**: PIS/PASEP com máscara
  - Nome: Beatriz Lima
  - CPF: `333.444.555-66`
  - PIS/PASEP: `999.88777.66-5`
  - **Esperado**: ✅ Criar com sucesso (PIS também limpo)

---

### 3️⃣ EDIÇÃO DE PESSOA

**URL**: http://127.0.0.1:8000/employees/{id}/edit

- [ ] **Teste 3.1**: Editar CPF com nova máscara
  - Abrir pessoa existente
  - Alterar CPF: `444.555.666-77`
  - **Esperado**: ✅ Atualizar com sucesso

- [ ] **Teste 3.2**: Tentar CPF duplicado na edição
  - Abrir pessoa existente
  - Alterar CPF: `123.456.789-00` (já existe em outra pessoa)
  - **Esperado**: ❌ Erro: "O cpf já está em uso."

- [ ] **Teste 3.3**: Manter CPF original na edição
  - Abrir pessoa existente
  - Não alterar CPF
  - Mudar apenas Nome
  - **Esperado**: ✅ Atualizar com sucesso (ignora unique próprio)

---

### 4️⃣ CASOS EXTREMOS

- [ ] **Teste 4.1**: CPF com espaços
  - CPF: `123. 456. 789-00`
  - **Esperado**: ✅ Criar com sucesso (espaços removidos)

- [ ] **Teste 4.2**: CPF com caracteres especiais
  - CPF: `123@456#789$00`
  - **Esperado**: ✅ Criar com CPF: `12345678900`

- [ ] **Teste 4.3**: CPF vazio
  - CPF: (deixar vazio)
  - **Esperado**: ❌ Erro: "O campo cpf é obrigatório."

- [ ] **Teste 4.4**: CPF incompleto após limpeza
  - CPF: `123.456.789`
  - **Esperado**: ❌ Erro: "O campo cpf deve ter 11 caracteres."

- [ ] **Teste 4.5**: PIS/PASEP vazio (opcional)
  - CPF: `555.666.777-88`
  - PIS/PASEP: (vazio)
  - **Esperado**: ✅ Criar com sucesso (PIS é opcional)

---

## 🔍 VALIDAÇÕES NO BANCO DE DADOS

Após os testes, verificar no banco:

```sql
-- Ver pessoas criadas
SELECT id, full_name, cpf, pis_pasep, created_at 
FROM people 
ORDER BY created_at DESC 
LIMIT 10;

-- Verificar se CPF está limpo (11 dígitos)
SELECT id, full_name, cpf, LENGTH(cpf) as cpf_length
FROM people
WHERE LENGTH(cpf) != 11;
-- Esperado: 0 resultados (todos devem ter exatamente 11)

-- Verificar unicidade de CPF
SELECT cpf, COUNT(*) as duplicates
FROM people
GROUP BY cpf
HAVING COUNT(*) > 1;
-- Esperado: 0 resultados (sem duplicatas)
```

---

## 📊 RESULTADO DOS TESTES

### ✅ Testes Passaram:
- [ ] Todos os testes de cadastro (1.1 a 1.4)
- [ ] Todos os testes com vínculo (2.1 a 2.2)
- [ ] Todos os testes de edição (3.1 a 3.3)
- [ ] Todos os casos extremos (4.1 a 4.5)
- [ ] Validações do banco de dados

### ❌ Testes Falharam:
_(Anotar aqui qualquer teste que falhou)_

---

## 🚨 ALERTAS IMPORTANTES

### ⚠️ O que NÃO foi alterado:
- **EmployeeImportController**: Já usa `_cleaned` (correto)
- **AuthController**: Login por CPF (já remove formatação)
- **Busca/filtros**: Já tratam formatação

### ✅ O que foi corrigido:
- **EmployeeController.store()**: Cria pessoa
- **EmployeeController.update()**: Edita pessoa
- **Ambos agora limpam ANTES de validar**

---

## 🎯 CRITÉRIOS DE SUCESSO

A correção é considerada bem-sucedida se:

1. ✅ **Criar pessoa** com CPF formatado funciona
2. ✅ **Editar pessoa** com CPF formatado funciona
3. ✅ **PIS/PASEP** formatado também funciona
4. ✅ **Validação de CPF único** continua funcionando
5. ✅ **Mensagens de erro** são claras e corretas
6. ✅ **Dados no banco** estão sempre limpos (11 dígitos)
7. ✅ **Nenhuma regressão** em funcionalidades existentes

---

## 📝 NOTAS ADICIONAIS

### Comportamento da Máscara JavaScript:
```javascript
// A máscara do formulário formata automaticamente:
Input:  "12345678900"
Output: "123.456.789-00"
```

### Comportamento do Laravel (após correção):
```php
// $request->merge() limpa antes de validar:
Recebe:  "123.456.789-00"
Limpa:   "12345678900"
Valida:  size:11 ✅ PASSA
Salva:   "12345678900"
```

---

```
╔═══════════════════════════════════════════════════════════════════╗
║                                                                   ║
║            🧪 EXECUTE TODOS OS TESTES ACIMA! 🧪                   ║
║                                                                   ║
║          Marque cada checkbox [x] conforme testar                 ║
║                                                                   ║
╚═══════════════════════════════════════════════════════════════════╝
```

**Status**: 📝 AGUARDANDO TESTES  
**Responsável**: Usuário deve executar e reportar resultados  
**Próximo Passo**: Marcar checkboxes após cada teste
