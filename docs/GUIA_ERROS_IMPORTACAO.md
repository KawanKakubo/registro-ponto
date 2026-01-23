# 📋 GUIA: Erros na Importação de Colaboradores

**Data**: 04/11/2025  
**Versão**: 1.0

---

## 🔍 ENTENDENDO OS ERROS DE IMPORTAÇÃO

### Por Que Ocorrem Erros?

Dos **636 registros** da sua importação:
- ✅ **403 criados** com sucesso
- ✅ **35 atualizados** com sucesso  
- ❌ **198 com erros**

Os erros acontecem quando os dados do CSV não atendem às regras de validação do sistema.

---

## 📊 CAUSAS MAIS COMUNS DE ERROS

### 1️⃣ **CPF Inválido** (Mais comum)

**Problema**:
```
O campo cpf deve ter 11 caracteres.
The cpf has already been taken.
```

**Causas**:
- CPF com menos de 11 dígitos
- CPF com letras ou caracteres especiais (após limpeza)
- CPF vazio
- CPF duplicado no próprio arquivo CSV

**Como Resolver**:
```csv
❌ ERRADO:
123.456.789-0    → Apenas 10 dígitos
abc.456.789-00   → Contém letras
                 → Vazio

✅ CORRETO:
123.456.789-00   → 11 dígitos (formatação será removida)
00000000000      → 11 dígitos sem formatação
```

---

### 2️⃣ **PIS/PASEP Inválido**

**Problema**:
```
O campo pis pasep deve ter 11 caracteres.
The pis pasep has already been taken.
```

**Causas**:
- PIS com menos de 11 dígitos
- PIS vazio quando deveria estar preenchido
- PIS duplicado no sistema

**Como Resolver**:
```csv
❌ ERRADO:
123.4567.89-0    → Apenas 10 dígitos
                 → Vazio (obrigatório)

✅ CORRETO:
123.45678.90-1   → 11 dígitos
12345678901      → 11 dígitos sem formatação
```

---

### 3️⃣ **Matrícula Inválida**

**Problema**:
```
O campo matricula é obrigatório.
O campo matricula não pode ter mais de 20 caracteres.
```

**Causas**:
- Matrícula vazia
- Matrícula muito longa (mais de 20 caracteres)
- Espaços extras

**Como Resolver**:
```csv
❌ ERRADO:
                           → Vazio
123456789012345678901      → Mais de 20 caracteres

✅ CORRETO:
001                        → Curto e válido
FNC-2024-12345            → Alfanumérico válido
```

---

### 4️⃣ **Estabelecimento Não Existe**

**Problema**:
```
O establishment_id selecionado é inválido.
```

**Causa**:
- O ID do estabelecimento no CSV não existe no banco de dados

**Como Resolver**:
1. Verificar IDs válidos:
   ```sql
   SELECT id, corporate_name FROM establishments ORDER BY id;
   ```

2. Usar apenas IDs que existem:
   ```csv
   ❌ ERRADO:
   999  → Estabelecimento não existe
   
   ✅ CORRETO:
   1    → ID válido do banco
   2    → ID válido do banco
   ```

---

### 5️⃣ **Departamento Não Existe**

**Problema**:
```
O department_id selecionado é inválido.
```

**Causa**:
- O ID do departamento no CSV não existe no banco de dados

**Como Resolver**:
1. Verificar IDs válidos:
   ```sql
   SELECT id, name FROM departments ORDER BY id;
   ```

2. Deixar vazio se não tiver departamento:
   ```csv
   ✅ CORRETO:
   1     → ID válido
         → Vazio (opcional)
   ```

---

### 6️⃣ **Data de Admissão Inválida**

**Problema**:
```
O campo admission_date deve ser uma data válida.
O campo admission_date é obrigatório.
```

**Causas**:
- Data em formato errado
- Data vazia
- Data inválida (ex: 31/02/2024)

**Como Resolver**:
```csv
❌ ERRADO:
2024-13-01       → Mês inválido
31/02/2024       → Dia inválido
01/01/24         → Ano com 2 dígitos
                 → Vazio

✅ CORRETO:
2024-01-15       → Formato YYYY-MM-DD
01/01/2024       → Formato DD/MM/YYYY
15-01-2024       → Formato DD-MM-YYYY
```

---

### 7️⃣ **Nome Completo Inválido**

**Problema**:
```
O campo full_name é obrigatório.
O campo full_name não pode ter mais de 255 caracteres.
```

**Causas**:
- Nome vazio
- Nome muito longo (mais de 255 caracteres)

**Como Resolver**:
```csv
❌ ERRADO:
                     → Vazio
João                 → Só primeiro nome (melhor usar completo)

✅ CORRETO:
João da Silva
Maria Oliveira Santos
José Carlos de Souza
```

---

## 🔧 COMO VER OS ERROS DETALHADOS

### 1. Acessar Detalhes da Importação

**URL**: `http://127.0.0.1:8000/employee-imports/{id}`

Onde `{id}` é o número da importação (no seu caso: **2**)

### 2. Seção "Detalhes dos Erros"

A página agora mostra uma seção com:
- **Número da linha** que teve erro
- **Lista de erros** encontrados naquela linha
- **Descrição clara** do que está errado

Exemplo:
```
┌──────────────────────────────────────────────────┐
│ Linha 45                                         │
│ 2 erros encontrados:                             │
│ • O campo cpf deve ter 11 caracteres.            │
│ • O campo pis pasep deve ter 11 caracteres.      │
└──────────────────────────────────────────────────┘
```

---

## 📝 PROCESSO PARA CORRIGIR ERROS

### Passo 1: Baixar Arquivo Original
Mantenha uma cópia do arquivo CSV original que foi importado.

### Passo 2: Ver Detalhes dos Erros
Acesse a página de detalhes da importação e anote:
- Números das linhas com erro
- Tipos de erro em cada linha

### Passo 3: Corrigir no Excel/CSV
Abra o arquivo CSV e corrija as linhas indicadas:

**Exemplo Prático**:
```
Erro reportado: "Linha 45 - O campo cpf deve ter 11 caracteres"

No CSV:
Linha 45: João Silva, 123.456.789-0, ...
                      └─ Apenas 10 dígitos!

Correção:
Linha 45: João Silva, 123.456.789-00, ...
                      └─ 11 dígitos ✓
```

### Passo 4: Reprocessar Apenas Linhas com Erro
Crie um novo arquivo CSV contendo APENAS as linhas que deram erro (corrigidas).

### Passo 5: Importar Novamente
Faça uma nova importação com o arquivo corrigido.

---

## 🔍 VALIDAÇÕES DO SISTEMA

### Tabela Completa de Regras:

| Campo              | Obrigatório | Tipo      | Tamanho Máx | Validação Extra              |
|--------------------|-------------|-----------|-------------|------------------------------|
| **cpf**            | ✅ Sim      | Numérico  | 11 dígitos  | Único no sistema             |
| **full_name**      | ✅ Sim      | Texto     | 255 chars   | -                            |
| **pis_pasep**      | ✅ Sim      | Numérico  | 11 dígitos  | Único no sistema             |
| **matricula**      | ✅ Sim      | Texto     | 20 chars    | Único no sistema             |
| **establishment_id** | ✅ Sim    | Número    | -           | Deve existir no banco        |
| **department_id**  | ❌ Não      | Número    | -           | Se preenchido, deve existir  |
| **admission_date** | ✅ Sim      | Data      | -           | Formato válido               |
| **role**           | ❌ Não      | Texto     | 255 chars   | Cargo/função                 |

---

## 💡 DICAS PARA EVITAR ERROS

### ✅ Antes de Importar:

1. **Use o Modelo CSV**
   - Baixe o modelo do sistema
   - Copie os dados para o modelo
   - Não altere os cabeçalhos

2. **Valide os Dados no Excel**
   - Verifique se CPFs têm 11 dígitos
   - Confirme que PIS/PASEP têm 11 dígitos
   - Verifique datas (não pode ter 31/02, por exemplo)

3. **Teste com Poucos Registros Primeiro**
   - Importe 5-10 linhas primeiro
   - Se funcionar, importe o restante

4. **Use a Pré-visualização**
   - O sistema mostra erros ANTES de processar
   - Corrija os erros indicados
   - Só confirme quando não houver erros

### ✅ Durante a Importação:

1. **Não Duplique Dados**
   - Não importe o mesmo CPF duas vezes
   - Não importe a mesma matrícula duas vezes

2. **Confira IDs de Estabelecimentos**
   - Liste os estabelecimentos cadastrados
   - Use apenas IDs válidos

3. **Padronize Datas**
   - Use sempre o mesmo formato
   - Recomendado: `YYYY-MM-DD` (2024-01-15)

---

## 📈 ESTATÍSTICAS DA SUA IMPORTAÇÃO

```
Total de Linhas:    636
├─ Criados:         403 (63.4%) ✅
├─ Atualizados:      35 (5.5%)  ✅
└─ Erros:           198 (31.1%) ❌

Taxa de Sucesso:    68.9%
Taxa de Erro:       31.1%
```

### Análise:
- **68.9% de sucesso** é uma taxa razoável para primeira importação
- Os **198 erros** provavelmente são causados por:
  - CPFs/PIS incompletos ou inválidos
  - IDs de estabelecimentos/departamentos inexistentes
  - Datas em formato incorreto
  - Matrículas duplicadas

---

## 🎯 PRÓXIMOS PASSOS

### Para Corrigir os 198 Erros:

1. **Acesse a página de detalhes**:
   ```
   http://127.0.0.1:8000/employee-imports/2
   ```

2. **Role até "Detalhes dos Erros"**
   - Leia cada erro cuidadosamente
   - Anote os números das linhas

3. **Agrupe por Tipo de Erro**:
   - Quantos são erros de CPF?
   - Quantos são erros de PIS?
   - Quantos são erros de establishment_id?

4. **Corrija em Massa**:
   - Se muitos erros são do mesmo tipo, corrija todos de uma vez
   - Use fórmulas do Excel para corrigir padrões

5. **Reimporte**:
   - Crie CSV apenas com linhas corrigidas
   - Importe novamente

---

## 🆘 CASOS ESPECIAIS

### Se TODOS os erros forem de CPF/PIS:
```bash
# Possível causa: arquivo veio com formatação errada
# Solução: Use função do Excel para limpar:
=SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(A1,".",""),"-","")," ","")
```

### Se TODOS os erros forem de establishment_id:
```bash
# Possível causa: IDs mudaram ou estabelecimentos não existem
# Solução: Liste os estabelecimentos válidos:
SELECT id, corporate_name FROM establishments;
# E atualize o CSV com IDs corretos
```

### Se TODOS os erros forem de data:
```bash
# Possível causa: formato de data errado
# Solução: Use formato YYYY-MM-DD
# Excel: =TEXT(A1,"YYYY-MM-DD")
```

---

## 📞 SUPORTE

Se após seguir este guia ainda houver dúvidas:

1. **Exporte a lista de erros** (screenshot da seção de erros)
2. **Anote os 5 primeiros erros**
3. **Verifique o arquivo CSV original**
4. **Compare com as regras de validação**

---

```
╔═══════════════════════════════════════════════════════════╗
║                                                           ║
║  🎯 AGORA VOCÊ PODE VER OS DETALHES DOS ERROS! 🎯       ║
║                                                           ║
║     Acesse: /employee-imports/2                          ║
║     Role até: "Detalhes dos Erros"                       ║
║                                                           ║
║  Você verá EXATAMENTE o que está errado em cada linha    ║
║                                                           ║
╚═══════════════════════════════════════════════════════════╝
```

**Criado em**: 04/11/2025  
**Última Atualização**: 04/11/2025  
**Versão do Sistema**: Laravel 12.36.0
