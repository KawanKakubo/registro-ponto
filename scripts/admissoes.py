#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Extrai e normaliza datas de admissão de um arquivo 'admissoes.txt'.
Remove cabeçalhos e converte todas as datas para o formato YYYY-MM-DD.
Gera 'admissoes_limpo.txt' com uma data por linha.
"""

import re
from datetime import datetime

INPUT = "admissoes.txt"
OUTPUT = "admissoes_limpo.txt"

# regex para capturar datas no formato dd/mm/aaaa
date_regex = re.compile(r"\b(\d{2})/(\d{2})/(\d{4})\b")

def extract_dates(text: str):
    dates = []
    for raw_line in text.splitlines():
        line = raw_line.strip()
        if not line or line.lower().startswith("admiss"):  # ignora "Admissão" e vazias
            continue
        match = date_regex.search(line)
        if not match:
            continue
        d, m, y = match.groups()
        try:
            iso_date = datetime.strptime(f"{d}/{m}/{y}", "%d/%m/%Y").strftime("%Y-%m-%d")
            dates.append(iso_date)
        except ValueError:
            # ignora datas inválidas, caso apareçam
            continue
    return dates

def main():
    try:
        with open(INPUT, "r", encoding="utf-8") as f:
            text = f.read()
    except FileNotFoundError:
        print(f"Arquivo '{INPUT}' não encontrado. Cole seu texto nele e execute novamente.")
        return

    dates = extract_dates(text)

    if not dates:
        print("Nenhuma data identificada (verifique o formato do arquivo).")
        return

    with open(OUTPUT, "w", encoding="utf-8") as f:
        f.write("\n".join(dates))

    print(f"{len(dates)} datas de admissão gravadas em '{OUTPUT}'.")

if __name__ == "__main__":
    main()
