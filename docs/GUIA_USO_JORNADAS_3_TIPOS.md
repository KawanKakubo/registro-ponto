# 📖 GUIA DE USO - SISTEMA DE JORNADAS (3 TIPOS)

**Versão:** 2.0  
**Data:** 01/11/2025

---

## 🎯 VISÃO GERAL

O sistema agora suporta **3 tipos** de jornadas de trabalho para atender todos os perfis de colaboradores da prefeitura.

---

## 📋 QUANDO USAR CADA TIPO

### 🔵 TIPO 1: Jornada Semanal Fixa
**Use quando:**
- O colaborador trabalha em **horários fixos** todos os dias
- Tem dias da semana definidos (ex: segunda a sexta)
- Horários não mudam (ex: sempre 08:00-12:00)

**Exemplos:**
- Pessoal administrativo
- Recepcionistas
- Secretários
- Tesouraria

### 🟣 TIPO 2: Escala de Revezamento
**Use quando:**
- O colaborador trabalha em **plantões rotativos**
- Segue um ciclo de trabalho/descanso (ex: 12x36, 24x72)
- Não trabalha dias fixos da semana

**Exemplos:**
- Enfermeiros (12x36)
- Médicos plantonistas (24x72)
- SAMU (24x72)
- Defesa Civil

### 🟢 TIPO 3: Carga Horária Semanal
**Use quando:**
- O colaborador tem **total de horas** por semana/mês
- Horários são **flexíveis** (não fixos)
- Não importa QUANDO trabalhou, mas SIM QUANTAS HORAS

**Exemplos:**
- Professores (20h, 30h, 40h)
- Pedagogos
- Coordenadores educacionais
- Consultores

---

## 🚀 COMO CRIAR UMA JORNADA

### Passo 1: Acessar o Menu
```
Menu Principal → Jornadas de Trabalho → Criar Nova Jornada
```

### Passo 2: Escolher o Tipo
Você verá 3 cards grandes. Clique no tipo apropriado:

```
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│  📅 SEMANAL      │  │  🔄 ESCALA       │  │  ⏱️ CARGA HORÁRIA│
│     FIXA         │  │  REVEZAMENTO     │  │    SEMANAL       │
│                  │  │                  │  │                  │
│ Horários fixos   │  │ Plantões 12x36   │  │ Professores      │
│ por dia da       │  │ ou 24x72         │  │ 20h ou 30h       │
│ semana           │  │                  │  │                  │
│                  │  │                  │  │                  │
│  [Selecionar]    │  │  [Selecionar]    │  │  [Selecionar]    │
└──────────────────┘  └──────────────────┘  └──────────────────┘
```

### Passo 3: Preencher o Formulário

#### Se escolheu SEMANAL FIXA:
1. **Nome:** Ex: "Administrativo Padrão 40h"
2. **Carga Horária Semanal:** Ex: 40
3. **Marque os dias** que trabalha (segunda a sexta)
4. **Preencha horários** de cada dia:
   - Entrada 1: 08:00
   - Saída 1: 12:00
   - Entrada 2: 13:00
   - Saída 2: 17:00

#### Se escolheu ESCALA REVEZAMENTO:
1. **Nome:** Ex: "Enfermeiros 12x36 - Hospital"
2. **Descrição:** Ex: "Escala para enfermagem"
3. **Dias de Trabalho:** 1 (trabalha 1 dia)
4. **Dias de Descanso:** 2 (descansa 2 dias)
5. **Horário do Plantão:**
   - Início: 19:00
   - Término: 07:00 (próximo dia)
6. Marque ☑ "Validar horário exato" se quiser controlar atrasos

#### Se escolheu CARGA HORÁRIA:
1. **Nome:** Ex: "Professor 20h Semanal"
2. **Descrição:** Ex: "Carga horária para docentes"
3. **Carga Horária Semanal:** 20
4. **Período de Apuração:** Semanal
5. **Tolerância:** 15 minutos
6. (Opcional) Marque se quer exigir mínimo de horas por dia

### Passo 4: Salvar
Clique em **"Criar Modelo"** e pronto!

---

## 👥 COMO APLICAR A JORNADA EM UM COLABORADOR

### Passo 1: Acessar o Colaborador
```
Menu → Colaboradores → [Selecione o colaborador]
```

### Passo 2: Ir em Jornada
```
Detalhes do Colaborador → Jornada de Trabalho
```

### Passo 3: Aplicar Template
1. Clique em **"Aplicar Template"**
2. Escolha o modelo na lista
3. Defina a **data de início** (ex: 01/11/2025)
4. **IMPORTANTE para Escalas:** Defina a "Data de Início do Ciclo"
   - Esta data determina quando o colaborador trabalha
   - Exemplo: Se 3 enfermeiros fazem 12x36, coloque datas diferentes:
     - Enfermeiro A: 01/11/2025 (trabalha dia 1, 4, 7...)
     - Enfermeiro B: 02/11/2025 (trabalha dia 2, 5, 8...)
     - Enfermeiro C: 03/11/2025 (trabalha dia 3, 6, 9...)
5. Clique em **"Aplicar"**

---

## 📊 COMO O SISTEMA CALCULA O PONTO

### TIPO 1: Semanal Fixa
**O sistema compara as batidas com os horários esperados:**
```
Horário esperado: 08:00-12:00, 13:00-17:00
Batida real:      08:15-12:00, 13:00-17:30

Resultado:
- Atraso: 15 minutos (entrou 08:15)
- Hora extra: 30 minutos (saiu 17:30)
```

### TIPO 2: Escala Revezamento
**O sistema calcula se é dia de trabalho baseado no ciclo:**
```
Escala 12x36: 1 trabalho, 2 descanso
Início do ciclo: 01/11/2025

Calendário:
01/11 → Posição 0 → TRABALHA ✅
02/11 → Posição 1 → FOLGA ❌
03/11 → Posição 2 → FOLGA ❌
04/11 → Posição 0 → TRABALHA ✅ (ciclo reinicia)

Se for dia de trabalho:
- Valida se bateu ponto
- Calcula se cumpriu as 12h
```

### TIPO 3: Carga Horária Semanal
**O sistema soma TODAS as horas da semana:**
```
Professor 20h:
Segunda:   4h (08:00-12:00)
Terça:     5h (13:00-18:00)
Quarta:    Não trabalhou
Quinta:    6h (08:00-14:00)
Sexta:     5h (13:00-18:00)

TOTAL: 20h ✅ Completou a carga
```

---

## 🎨 IDENTIFICANDO O TIPO NA LISTA

Na tela de **Modelos de Jornada**, você verá badges coloridos:

- 🔵 **Badge Azul:** Jornada Semanal Fixa
- 🟣 **Badge Roxo:** Escala de Revezamento
- 🟢 **Badge Verde:** Carga Horária Semanal

Cada badge mostra informações específicas:
- **Azul:** "40h/semana | Seg-Sex"
- **Roxo:** "12x36 | 12h plantão"
- **Verde:** "20h/semana | Apuração semanal"

---

## 🔍 EXEMPLOS PRÁTICOS

### Exemplo 1: Secretária de Escola
**Perfil:** Trabalha segunda a sexta, 08:00-17:00 com 1h de almoço

**Tipo a usar:** 🔵 Jornada Semanal Fixa

**Configuração:**
```
Nome: Administrativo Escolar 40h
Tipo: Semanal
Carga: 40h

Segunda a Sexta:
- Entrada 1: 08:00
- Saída 1: 12:00
- Entrada 2: 13:00
- Saída 2: 17:00
```

### Exemplo 2: Enfermeiro do Hospital
**Perfil:** Trabalha 1 dia, folga 2 dias, plantão noturno de 12h

**Tipo a usar:** 🟣 Escala de Revezamento

**Configuração:**
```
Nome: Enfermeiros 12x36 - Hospital
Tipo: Escala
Dias de Trabalho: 1
Dias de Descanso: 2
Horário: 19:00 - 07:00
```

**Ao aplicar:**
```
3 enfermeiros no revezamento:
- Maria: cycle_start = 01/11 (trabalha 01, 04, 07, 10...)
- João: cycle_start = 02/11 (trabalha 02, 05, 08, 11...)
- Ana: cycle_start = 03/11 (trabalha 03, 06, 09, 12...)
```

### Exemplo 3: Professor 20h
**Perfil:** 20 horas semanais, horários variáveis

**Tipo a usar:** 🟢 Carga Horária Semanal

**Configuração:**
```
Nome: Professor 20h Semanal
Tipo: Carga Horária
Carga: 20h/semana
Período: Semanal
```

**Como funciona:**
```
O professor pode trabalhar:
- Seg: 4h
- Ter: 5h
- Qua: 0h
- Qui: 6h
- Sex: 5h
Total: 20h ✅

Ou pode distribuir diferente:
- Seg: 8h
- Ter: 8h
- Qua: 4h
- Qui: 0h
- Sex: 0h
Total: 20h ✅

O sistema só verifica se o TOTAL da semana deu 20h!
```

---

## ⚠️ DICAS IMPORTANTES

### Para Escalas de Revezamento:
1. **Sempre defina a data de início do ciclo** ao aplicar no colaborador
2. Use **datas diferentes** para colaboradores que revezam
3. Para 12x36 com 3 pessoas: use 01/11, 02/11, 03/11
4. Para 24x72 com 4 pessoas: use 01/11, 02/11, 03/11, 04/11

### Para Carga Horária:
1. O sistema **não valida horários fixos**
2. Só importa o **total de horas** no período
3. Configure a **tolerância** para considerar faltas
4. Se trabalhou menos de 15 min, considera que não trabalhou

### Para Semanal Fixa:
1. Desmarque o checkbox **"Dia de trabalho"** para folgas
2. Você pode ter horários diferentes em cada dia
3. Sistema calcula **atrasos e horas extras** automaticamente

---

## 📞 SUPORTE

**Dúvidas?** Contate o setor de TI:
- Email: ti@prefeitura.gov.br
- Ramal: 1234

---

**Desenvolvido para:** Prefeitura Municipal  
**Versão:** 2.0 - Sistema Multi-Jornadas  
**Data:** Novembro/2025
