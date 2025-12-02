# ⚠️ RELATÓRIO - Dados Faltantes no Banco

## 🔍 Situação Identificada

Durante a análise do banco de dados após a importação do CSV, foram identificados colaboradores com dados incompletos (sem CPF, sem cargo, sem departamento).

---

## 📊 Números

```
┌─────────────────────────────────────────────────────────────────┐
│  📈 ESTATÍSTICAS DO BANCO DE DADOS                              │
├─────────────────────────────────────────────────────────────────┤
│  Total de Pessoas:                      993                     │
│  Total de Vínculos:                   1.005                     │
│                                                                 │
│  ❌ DADOS FALTANTES                                             │
│    • Pessoas sem CPF:                   530                     │
│    • Vínculos sem Cargo:                441                     │
│    • Vínculos sem Departamento:         462                     │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  📄 ARQUIVO CSV IMPORTADO                                       │
├─────────────────────────────────────────────────────────────────┤
│  Total de linhas:                       637                     │
│  Matrículas únicas:                     592                     │
│  Taxa de sucesso:                     99,8%                     │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 Causa do Problema

### Os dados incompletos **NÃO são do CSV importado**

O arquivo CSV que você forneceu (`importacao-colaboradores.csv`) contém todos os dados completos:
- ✅ CPF
- ✅ PIS
- ✅ Cargo
- ✅ Departamento
- ✅ Matrícula
- ✅ Estabelecimento
- ✅ Data de admissão

**Conclusão:** Os colaboradores com dados faltantes **JÁ EXISTIAM NO BANCO** antes da importação do CSV e **NÃO estão no arquivo CSV**.

---

## 🔢 Análise Quantitativa

```
┌─────────────────────────────────────────────────────────────────┐
│  MATRÍCULAS NO BANCO:           1.005 vínculos                  │
│  MATRÍCULAS NO CSV:               592 vínculos                  │
│  ───────────────────────────────────────────────────────────────│
│  DIFERENÇA:                       413 vínculos                  │
│                                                                 │
│  Esses 413 vínculos NÃO estão no CSV fornecido!                │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📋 Exemplos de Colaboradores Sem Dados

### Vínculos sem Cargo

```
Matrícula 3406 - ANDREZZA KARINE SPANHOL DA SILVA
  • CPF: (vazio)
  • PIS: 12772363521
  • Cargo: (vazio)
  • Departamento: NULL
  • STATUS: Não está no CSV importado

Matrícula 3442 - ANDRIELLI CUNHA SAMPAIO
  • CPF: (vazio)
  • Cargo: (vazio)
  • Departamento: NULL
  • STATUS: Não está no CSV importado

Matrícula 1677 - ANDRÉA DE OLIVEIRA SOUZA
  • CPF: (vazio)
  • Cargo: (vazio)
  • Departamento: NULL
  • STATUS: Não está no CSV importado
```

---

## 💡 Origem dos Dados Incompletos

Esses dados provavelmente vieram de:

1. **Importação anterior de AFD**
   - Arquivos AFD podem ter apenas PIS/Matrícula
   - Não incluem cargo ou departamento

2. **Importação manual antiga**
   - Dados inseridos manualmente sem validação
   - Campos opcionais deixados vazios

3. **Migração de sistema legado**
   - Dados importados de sistema antigo
   - Informações incompletas na origem

---

## ✅ Solução

### Opção 1: Completar o CSV e Reimportar

**Recomendado se você tem os dados completos**

1. Adicionar as 413 matrículas faltantes ao CSV
2. Incluir todos os dados (CPF, cargo, departamento)
3. Executar novamente o seeder

```bash
# Após adicionar todos os colaboradores ao CSV
php artisan db:seed --class=EmployeesFromCsvSeeder
```

### Opção 2: Criar Script de Limpeza

**Para remover vínculos sem dados essenciais**

```php
// Remover vínculos sem cargo E sem departamento
EmployeeRegistration::whereNull('position')
    ->whereNull('department_id')
    ->delete();
```

⚠️ **ATENÇÃO:** Isso pode remover vínculos que têm registros de ponto!

### Opção 3: Manter Como Está

**Se os dados incompletos são históricos/inativos**

- Deixar os registros no banco
- Marcar como inativos
- Filtrar nas consultas

```php
// Buscar apenas vínculos com dados completos
$vinculos = EmployeeRegistration::whereNotNull('position')
    ->whereNotNull('department_id')
    ->where('status', 'active')
    ->get();
```

---

## 🔍 Como Identificar Quais Matrículas Faltam

### Exportar matrículas do banco

```bash
php artisan tinker --execute="
\$matriculas = App\Models\EmployeeRegistration::pluck('matricula')->sort()->values();
file_put_contents('matriculas_banco.txt', \$matriculas->implode('\n'));
echo 'Exportado para matriculas_banco.txt\n';
"
```

### Comparar com o CSV

```bash
# Extrair matrículas do CSV
awk -F',' 'NR>1 {print $4}' importacao-colaboradores.csv | sort > matriculas_csv.txt

# Ver matrículas que estão no banco mas não no CSV
comm -23 <(sort matriculas_banco.txt) <(sort matriculas_csv.txt) > matriculas_faltantes.txt

# Ver quantas são
wc -l matriculas_faltantes.txt
```

---

## 📝 Recomendação

### 1. Verificar com o RH/Gestor

- As 413 matrículas faltantes ainda estão ativas?
- São colaboradores desligados?
- Precisam ser atualizados ou removidos?

### 2. Se Estão Ativos

- Solicitar CSV completo com TODAS as matrículas
- Incluir os 413 colaboradores faltantes
- Executar importação novamente

### 3. Se São Históricos/Inativos

- Marcar como `status = 'inactive'`
- Manter no banco para histórico de registros de ponto
- Não exibir em relatórios de colaboradores ativos

---

## 🎯 Consultas Úteis

### Ver todos os vínculos sem dados completos

```sql
SELECT 
    er.matricula,
    p.full_name,
    p.cpf,
    er.position,
    er.department_id,
    er.status
FROM employee_registrations er
LEFT JOIN people p ON p.id = er.person_id
WHERE 
    (p.cpf IS NULL OR p.cpf = '')
    OR (er.position IS NULL OR er.position = '')
    OR er.department_id IS NULL
ORDER BY er.matricula;
```

### Contar registros de ponto por vínculo incompleto

```sql
SELECT 
    er.matricula,
    p.full_name,
    COUNT(tr.id) as total_registros
FROM employee_registrations er
LEFT JOIN people p ON p.id = er.person_id
LEFT JOIN time_records tr ON tr.employee_registration_id = er.id
WHERE 
    (er.position IS NULL OR er.position = '')
GROUP BY er.id, er.matricula, p.full_name
HAVING COUNT(tr.id) > 0
ORDER BY total_registros DESC;
```

---

## ✅ Checklist de Ação

### Passo 1: Identificar
- [ ] Exportar lista de matrículas faltantes
- [ ] Verificar se são colaboradores ativos ou inativos
- [ ] Consultar RH/gestor sobre status desses colaboradores

### Passo 2: Decidir
- [ ] Opção A: Completar CSV com todos os colaboradores
- [ ] Opção B: Marcar como inativos
- [ ] Opção C: Remover do banco (se sem registros de ponto)

### Passo 3: Executar
- [ ] Implementar solução escolhida
- [ ] Validar resultados
- [ ] Documentar mudanças

---

## 📞 Próximos Passos

1. **URGENTE:** Verificar com RH se o CSV tem TODOS os colaboradores ativos
2. **SE NÃO:** Solicitar CSV completo
3. **SE SIM:** Os 413 registros incompletos são históricos e podem ser marcados como inativos

---

**Data do Relatório:** 02/12/2025  
**Situação:** ⚠️ REQUER AÇÃO - CSV incompleto ou dados legados no banco  
**Impacto:** Médio - Não afeta funcionamento mas dificulta gestão
