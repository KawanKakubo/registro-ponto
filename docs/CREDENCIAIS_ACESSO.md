# 🔐 CREDENCIAIS DE ACESSO - SISTEMA DE PONTO

**Data de Criação**: 04/11/2025  
**Sistema**: Ponto Digital Assaí  
**URL**: http://127.0.0.1:8000

---

## 👤 USUÁRIO ADMINISTRADOR

### Credenciais:
- **CPF**: `000.000.000-00` (ou `00000000000` sem formatação)
- **Senha**: `admin123`

### Informações do Usuário:
- **Nome**: Administrador
- **Email**: admin@assai.pr.gov.br
- **Perfil**: Administrador
- **Status**: Ativo
- **Estabelecimento**: Prefeitura Municipal de Assaí

---

## 🚀 COMO FAZER LOGIN

1. Acesse: http://127.0.0.1:8000/login
2. Digite o CPF: `000.000.000-00`
3. Digite a Senha: `admin123`
4. Clique em "Entrar"

---

## ⚠️ IMPORTANTE

### Primeira Vez Usando o Sistema:
- O campo CPF aceita formatação automática (000.000.000-00)
- Você também pode digitar apenas os números (00000000000)
- A senha é case-sensitive (sensível a maiúsculas/minúsculas)

### Segurança:
- ⚠️ **ALTERE A SENHA APÓS O PRIMEIRO ACESSO**
- Esta é uma senha padrão para desenvolvimento/testes
- Em produção, use senhas fortes e únicas

### Problemas de Acesso:
Se você receber a mensagem "CPF ou senha incorretos", verifique:
1. O CPF está correto: `00000000000` ou `000.000.000-00`
2. A senha está correta: `admin123` (tudo minúsculo)
3. O usuário foi criado corretamente (execute: `php artisan db:seed --class=UserSeeder`)

---

## 🔄 RECRIAR USUÁRIO ADMINISTRADOR

Se necessário, você pode recriar o usuário executando:

```bash
cd /home/kawan/Documents/areas/SECTI/registro-ponto
php artisan db:seed --class=UserSeeder
```

Este comando cria ou atualiza o usuário administrador.

---

## 📊 FUNCIONALIDADES DISPONÍVEIS APÓS LOGIN

### Dashboard Principal:
- ✅ Estatísticas consolidadas (Pessoas, Vínculos, Estabelecimentos, Marcações)
- ✅ 4 gráficos interativos (Chart.js)
- ✅ Sistema de alertas
- ✅ Ações rápidas
- ✅ Atividade recente

### Módulos Principais:
- 🏢 **Estabelecimentos**: Gerenciar estabelecimentos da empresa
- 👥 **Pessoas**: Cadastro de pessoas
- 🔗 **Vínculos**: Gerenciar vínculos de colaboradores
- 📅 **Jornadas**: Templates de jornada de trabalho
- 📊 **Cartões de Ponto**: Geração de cartões
- 📥 **Importações**: AFD e CSV

---

## 🛠️ COMANDOS ÚTEIS

### Verificar se o servidor está rodando:
```bash
php artisan serve
```

### Limpar cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Executar testes:
```bash
php artisan test
```

---

## 📞 SUPORTE

Em caso de dúvidas ou problemas:
1. Verifique a documentação em `INDICE_DOCUMENTACAO.md`
2. Consulte o guia rápido em `GUIA_RAPIDO_REFATORACAO.md`
3. Veja o status do sistema em `STATUS_FASE7.md`

---

```
╔═══════════════════════════════════════════════════════════════════╗
║                                                                   ║
║                   🔐 CREDENCIAIS CONFIGURADAS 🔐                 ║
║                                                                   ║
║                 CPF: 000.000.000-00                              ║
║                 Senha: admin123                                   ║
║                                                                   ║
║              Acesse: http://127.0.0.1:8000/login                 ║
║                                                                   ║
╚═══════════════════════════════════════════════════════════════════╝
```

**Última Atualização**: 04/11/2025  
**Versão do Sistema**: 1.6.0
