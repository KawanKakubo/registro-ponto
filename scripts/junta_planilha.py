import pandas as pd
import re # Biblioteca para usar expressões regulares (regex)

# --- 1. Função para Extrair Dados do Relatório "Zoado" com Regex ---

def extrair_dados_do_relatorio(caminho_arquivo):
    """
    Lê um arquivo de relatório mal formatado como texto simples, linha por linha,
    e usa uma regex precisa para extrair os dados dos funcionários.
    """
    print(f"Lendo e extraindo dados de: {caminho_arquivo}...")
    
    dados_dos_funcionarios = []
    
    # --- MUDANÇA PRINCIPAL AQUI ---
    # Regex ajustada para ser extremamente precisa com a quantidade de vírgulas
    # e a estrutura da linha de dados do funcionário.
    padrao_regex = re.compile(
        r","                  # Linha começa com uma vírgula
        r"(\d+/\d+)"          # Grupo 1: Matrícula (ex: 1484/0)
        r",,,,,"               # Exatamente 5 vírgulas
        r"([A-Z\s\.ÇÁÉÍÓÚÃÕÊÔ]+?)" # Grupo 2: Nome (letras maiúsculas, espaços, acentos, etc.)
        r",,"                  # Exatamente 2 vírgulas
        r"([0-9\.\-]+\s*)"    # Grupo 3: CPF (números, pontos, hífen e espaços no final)
        r","                  # 1 vírgula
        r"(\d+)"              # Grupo 4: PIS (números)
        r","                  # 1 vírgula
        r"(\d{2}/\d{2}/\d{4})" # Grupo 5: Data (dd/mm/yyyy)
    )

    try:
        with open(caminho_arquivo, 'r', encoding='latin-1') as arquivo:
            for i, linha in enumerate(arquivo):
                match = padrao_regex.search(linha)
                if match:
                    # Se a linha corresponde ao nosso padrão, extrai os grupos
                    nome = match.group(2).strip()
                    cpf = match.group(3).strip()
                    pis = match.group(4).strip()
                    data_admissao = match.group(5).strip()
                    
                    dados_dos_funcionarios.append({
                        'Nome': nome,
                        'CPF': cpf,
                        'PIS': pis,
                        'Data Admissão': data_admissao
                    })

        if not dados_dos_funcionarios:
            print("\n--- DEBUG ---")
            print("AVISO: Nenhum dado de funcionário foi encontrado com a nova regex.")
            print("Verifique se o formato das linhas abaixo corresponde ao esperado:")
            with open(caminho_arquivo, 'r', encoding='latin-1') as f:
                for i in range(30): # Mostra as primeiras 30 linhas do arquivo para depuração
                    print(f.readline().strip())
            print("--- FIM DEBUG ---\n")
            return None

        print(f"Extração concluída! {len(dados_dos_funcionarios)} registros de funcionários encontrados.")
        return pd.DataFrame(dados_dos_funcionarios)

    except Exception as e:
        print(f"Ocorreu um erro crítico ao ler o arquivo de relatório: {e}")
        return None

# --- 2. Carregar e Processar os Arquivos ---
df1_limpo = None
try:
    arquivo_dinamico = 'Listagem de funcionários - dinâmica.xls'
    arquivo_modelo = 'modelo-importacao-colaboradores.csv'

    df1_limpo = extrair_dados_do_relatorio(arquivo_dinamico)
    df2_modelo = pd.read_csv(arquivo_modelo)
    print(f"Arquivo '{arquivo_modelo}' carregado com sucesso.")

except FileNotFoundError as e:
    print(f"Erro: Arquivo não encontrado. Verifique o nome e o caminho: {e}")
    exit()

# --- 3. Juntar (Merge) os Dados ---

if df1_limpo is not None and not df1_limpo.empty:
    df_resultado = pd.merge(
        df2_modelo,
        df1_limpo,
        left_on='full_name',
        right_on='Nome',
        how='left'
    )
    
    if 'Nome' in df_resultado.columns:
        df_resultado.drop('Nome', axis=1, inplace=True)

    # --- 4. Salvar e Exibir o Resultado ---
    df_resultado.to_csv('resultado_final_comparado.csv', index=False, encoding='utf-8-sig')
    
    print("\n✨ Comparação finalizada com sucesso!")
    print("O resultado foi salvo no arquivo 'resultado_final_comparado.csv'")
    print("\nPré-visualização do resultado:")
    print(df_resultado.head())
else:
    print("\nNão foi possível realizar a comparação pois a extração de dados do relatório falhou.")