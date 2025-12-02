# 🚀 GUIA RÁPIDO: Importação de Vínculos

## ⚡ Início Rápido (5 Minutos)

### 1️⃣ Preparar o CSV

Crie um arquivo CSV com estas colunas:

```csv
NOME,Nº PIS/PASEP,Nº IDENTIFICADOR,HORÁRIO,HORÁRIO_LIMPO
João Silva,12345678901,M001,"7 - SAÚDE -07:30-11:30-13:00-17:00","7h/dia"
Maria Santos,98765432100,M002,"219 - SEC - 15-20 E 21-00","Secretaria"
```

### 2️⃣ Acessar o Sistema

```
Menu Lateral → EQUIPAMENTOS → Importar Vínculos
```

ou acesse diretamente:

```
http://seu-dominio/vinculo-imports/create
```

### 3️⃣ Fazer Upload

1. Clique em "Selecionar arquivo"
2. Escolha seu CSV
3. Clique em "Iniciar Importação"

**Pronto!** O sistema processará automaticamente em segundo plano.

### 4️⃣ Ver Resultados

Você será redirecionado para a tela de resultados que mostra:

- ✅ Total de linhas processadas
- 👤 Pessoas criadas/atualizadas
- 🆔 Vínculos criados/atualizados
- ⏰ Jornadas associadas
- ❌ Erros (se houver)

## 📋 Regras Importantes

### ✅ Campos Obrigatórios

- **NOME**: Nome completo da pessoa
- **Nº PIS/PASEP**: 11 dígitos (pode ter formatação)
- **Nº IDENTIFICADOR**: Matrícula única do vínculo
- **HORÁRIO**: Deve começar com o ID da jornada (ex: "7 - ...")

### ⚠️ O Que o Sistema Faz

1. **Busca a Pessoa pelo PIS**
   - Se não existe → Cria nova pessoa
   - Se existe → Atualiza o nome

2. **Busca o Vínculo pela Matrícula**
   - Se não existe → Cria novo vínculo
   - Se existe → Atualiza (não duplica!)

3. **Extrai o ID da Jornada**
   - Parser automático: "7 - SAÚDE..." → 7
   - Associa ao vínculo se o template existir

## 🔧 Solução de Problemas

### Erro: "Colunas obrigatórias faltando"

**Causa:** Header do CSV incorreto

**Solução:** Certifique-se que a primeira linha é:
```csv
NOME,Nº PIS/PASEP,Nº IDENTIFICADOR,HORÁRIO,HORÁRIO_LIMPO
```

### Erro: "PIS/PASEP é obrigatório"

**Causa:** Campo vazio na linha

**Solução:** Preencha o PIS/PASEP para todas as linhas

### Erro: "Matrícula é obrigatória"

**Causa:** Campo "Nº IDENTIFICADOR" vazio

**Solução:** Preencha a matrícula para todas as linhas

### Aviso: "Jornada ID X não encontrada"

**Causa:** O template de jornada não existe no sistema

**Solução:** 
1. Acesse "Modelos de Jornada"
2. Crie o template com o ID correto
3. Re-importe o CSV (vínculos serão atualizados)

## 📊 Ver Erros Detalhados

Se houver erros na importação:

1. Na tela de resultados, clique em "Ver Erros Detalhados"
2. Use a busca para encontrar linhas específicas
3. Clique em "Ver Detalhes" para info completa
4. Baixe o relatório de erros em CSV

## 🔄 Re-importar

Para corrigir erros e re-importar:

1. Baixe o relatório de erros
2. Corrija as linhas no CSV original
3. Faça novo upload
4. Sistema atualizará automaticamente (não duplica!)

## 💡 Dicas

### ✅ Antes de Importar

- Crie os templates de jornada primeiro
- Teste com um CSV pequeno (10-20 linhas)
- Valide os resultados
- Depois importe o arquivo completo

### ✅ Durante a Importação

- Não feche a página durante o processamento
- A tela atualiza automaticamente
- Grandes arquivos podem levar alguns minutos

### ✅ Depois da Importação

- Verifique as estatísticas
- Se houver erros, corrija e re-importe
- Valide alguns registros manualmente

## 📞 Precisa de Ajuda?

Consulte a documentação completa em:
- `IMPORTACAO_VINCULOS_JORNADAS.md`
- `ENTREGA_IMPORTACAO_VINCULOS.md`
