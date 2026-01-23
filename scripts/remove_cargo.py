# remove_cargo.py
with open("entrada.txt", "r", encoding="utf-8") as f:
    linhas = f.readlines()

# Remove linhas que contêm apenas "Cargo" (com ou sem espaços)
linhas_limpa = [linha for linha in linhas if linha.strip().upper() != "CARGO" and linha.strip() != ""]

with open("saida.txt", "w", encoding="utf-8") as f:
    f.writelines(linhas_limpa)

print("✅ Linhas com 'Cargo' foram removidas. Arquivo salvo como 'saida.txt'.")
