#!/bin/bash
# COMANDOS ÚTEIS - SISTEMA DE PONTO
# Execute com: bash COMANDOS_UTEIS.sh <comando>

case "$1" in
    "iniciar")
        echo "🚀 Iniciando sistema..."
        echo "Terminal 1: Servidor Web"
        php artisan serve &
        sleep 2
        echo "Terminal 2: Worker de Filas"
        php artisan queue:work --tries=3 --timeout=300
        ;;
        
    "worker")
        echo "🔄 Iniciando worker de filas..."
        php artisan queue:work --tries=3 --timeout=300
        ;;
        
    "limpar")
        echo "🧹 Limpando cache..."
        php artisan cache:clear
        php artisan config:clear
        php artisan route:clear
        php artisan view:clear
        echo "✅ Cache limpo!"
        ;;
        
    "migrar")
        echo "📊 Executando migrations..."
        php artisan migrate
        ;;
        
    "rotas")
        echo "🗺️ Listando rotas..."
        php artisan route:list
        ;;
        
    "jobs-falhos")
        echo "❌ Jobs falhados:"
        php artisan queue:failed
        ;;
        
    "reprocessar")
        echo "🔄 Reprocessando jobs falhados..."
        php artisan queue:retry all
        ;;
        
    "limpar-jobs")
        echo "🗑️ Limpando jobs falhados..."
        php artisan queue:flush
        ;;
        
    "logs")
        echo "📝 Exibindo logs (Ctrl+C para sair)..."
        tail -f storage/logs/laravel.log
        ;;
        
    "teste-csv")
        echo "📄 Criando arquivo CSV de teste..."
        cat > teste_colaboradores.csv << 'CSVEOF'
establishment_id,department_id,full_name,cpf,pis_pasep,admission_date,email,phone,status
1,1,João Silva Teste,11122233344,11122233344,2024-01-15,joao.teste@email.com,11987654321,active
1,1,Maria Santos Teste,22233344455,22233344455,2024-02-20,maria.teste@email.com,11987654322,active
1,2,Pedro Oliveira Teste,33344455566,33344455566,2024-03-10,pedro.teste@email.com,11987654323,active
CSVEOF
        echo "✅ Arquivo 'teste_colaboradores.csv' criado!"
        ;;
        
    "status")
        echo "📊 Status do Sistema"
        echo "===================="
        echo ""
        echo "Servidor:"
        ps aux | grep "php artisan serve" | grep -v grep && echo "✅ Rodando" || echo "❌ Parado"
        echo ""
        echo "Worker de Filas:"
        ps aux | grep "queue:work" | grep -v grep && echo "✅ Rodando" || echo "❌ Parado"
        echo ""
        echo "Banco de Dados:"
        php artisan db:show && echo "✅ Conectado" || echo "❌ Erro"
        ;;
        
    "backup")
        DATA=$(date +%Y%m%d_%H%M%S)
        echo "💾 Criando backup..."
        pg_dump registro-ponto > "backup_${DATA}.sql"
        tar -czf "storage_backup_${DATA}.tar.gz" storage/app/
        echo "✅ Backup criado: backup_${DATA}.sql e storage_backup_${DATA}.tar.gz"
        ;;
        
    *)
        echo "COMANDOS DISPONÍVEIS:"
        echo "===================="
        echo ""
        echo "bash COMANDOS_UTEIS.sh iniciar      - Inicia servidor e worker"
        echo "bash COMANDOS_UTEIS.sh worker       - Inicia apenas o worker"
        echo "bash COMANDOS_UTEIS.sh limpar       - Limpa todo o cache"
        echo "bash COMANDOS_UTEIS.sh migrar       - Executa migrations"
        echo "bash COMANDOS_UTEIS.sh rotas        - Lista todas as rotas"
        echo "bash COMANDOS_UTEIS.sh jobs-falhos  - Mostra jobs que falharam"
        echo "bash COMANDOS_UTEIS.sh reprocessar  - Reprocessa jobs falhados"
        echo "bash COMANDOS_UTEIS.sh limpar-jobs  - Remove jobs falhados"
        echo "bash COMANDOS_UTEIS.sh logs         - Exibe logs em tempo real"
        echo "bash COMANDOS_UTEIS.sh teste-csv    - Cria CSV de teste"
        echo "bash COMANDOS_UTEIS.sh status       - Mostra status do sistema"
        echo "bash COMANDOS_UTEIS.sh backup       - Cria backup do banco e storage"
        echo ""
        ;;
esac
