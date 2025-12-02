import re

with open("cpfs.txt", "r", encoding="utf-8") as f:
    texto = f.read()

# Remove tudo que não seja número, ponto ou traço
cpfs = re.findall(r"\d{3}\.\d{3}\.\d{3}-\d{2}", texto)

# Remove duplicados e ordena
# cpfs_unicos = sorted(set(cpfs))

# Salva em um novo arquivo, um por linha
with open("cpfs_limpos.txt", "w", encoding="utf-8") as f:
    f.write("\n".join(cpfs))
