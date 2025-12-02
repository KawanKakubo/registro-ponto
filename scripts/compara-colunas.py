import pandas as pd

def comparar_colunas(arquivo, coluna1, coluna2):
    """
    Compara duas colunas de uma tabela e identifica diferenças.
    
    Args:
        arquivo: Caminho para o arquivo (CSV, Excel, etc)
        coluna1: Nome da primeira coluna
        coluna2: Nome da segunda coluna
    """
    # Carregar o arquivo (ajuste conforme o tipo de arquivo)
    # Para CSV:
    df = pd.read_excel(arquivo)
    # Para Excel, use: df = pd.read_excel(arquivo)
    
    # Remover valores vazios/nulos
    valores_coluna1 = set(df[coluna1].dropna())
    valores_coluna2 = set(df[coluna2].dropna())
    
    # Encontrar diferenças
    apenas_coluna1 = valores_coluna1 - valores_coluna2
    apenas_coluna2 = valores_coluna2 - valores_coluna1
    valores_comuns = valores_coluna1 & valores_coluna2
    
    # Exibir resultados
    print(f"{'='*60}")
    print(f"ANÁLISE DE DIFERENÇAS ENTRE COLUNAS")
    print(f"{'='*60}\n")
    
    print(f"Coluna 1: {coluna1}")
    print(f"Coluna 2: {coluna2}\n")
    
    print(f"Total de valores únicos em '{coluna1}': {len(valores_coluna1)}")
    print(f"Total de valores únicos em '{coluna2}': {len(valores_coluna2)}")
    print(f"Valores em comum: {len(valores_comuns)}\n")
    
    print(f"{'-'*60}")
    print(f"Valores presentes APENAS em '{coluna1}' ({len(apenas_coluna1)}):")
    print(f"{'-'*60}")
    if apenas_coluna1:
        for valor in sorted(apenas_coluna1):
            print(f"  • {valor}")
    else:
        print("  (Nenhum)")
    
    print(f"\n{'-'*60}")
    print(f"Valores presentes APENAS em '{coluna2}' ({len(apenas_coluna2)}):")
    print(f"{'-'*60}")
    if apenas_coluna2:
        for valor in sorted(apenas_coluna2):
            print(f"  • {valor}")
    else:
        print("  (Nenhum)")
    
    # Retornar os conjuntos para uso posterior se necessário
    return {
        'apenas_coluna1': apenas_coluna1,
        'apenas_coluna2': apenas_coluna2,
        'comuns': valores_comuns
    }


# Exemplo de uso:
if __name__ == "__main__":
    # Substitua pelos seus valores
    arquivo = "TABELA.xlsx"  # ou .xlsx para Excel
    coluna1 = "nome1"
    coluna2 = "nome2"
    
    resultado = comparar_colunas(arquivo, coluna1, coluna2)
    
    # Opcional: Salvar resultado em arquivo
    with open("diferencas_encontradas.txt", "w", encoding="utf-8") as f:
        f.write(f"Apenas em {coluna1}:\n")
        for valor in sorted(resultado['apenas_coluna1']):
            f.write(f"{valor}\n")
        
        f.write(f"\nApenas em {coluna2}:\n")
        for valor in sorted(resultado['apenas_coluna2']):
            f.write(f"{valor}\n")
    
    print(f"\n{'='*60}")
    print("Resultados salvos em 'diferencas_encontradas.txt'")