#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Extrai nomes de um arquivo 'nomes.txt' produzido a partir do relatório.
Gera 'nomes_limpos.txt' com um nome por linha, sem cabeçalhos nem duplicatas.
"""

import re
from collections import OrderedDict

INPUT = "nomes.txt"
OUTPUT = "nomes_limpos.txt"

# Padrões de cabeçalho/ruído que queremos remover (case-insensitive)
BLACKLIST_PATTERNS = [
    r"ESTADO\s+DO\s+PARANÁ",
    r"PREFEITURA\s+MUNICIPAL",
    r"\[PUBINFOS\]",
    r"RELATÓRIO\s+DE\s+FUNCIONÁRIOS",
    r"\bNOME\b",
    r"RELAT[oó]RIO",
    r"ESTADO",
    r"PREFEITURA",
    r"PUBLICA",
    r"PAGE|P[AÁ]GINA",  # caso apareça paginação
    r"^\s*-$",          # linhas com apenas traço
]

# Compila regexes
blacklist_regex = re.compile("|".join(BLACKLIST_PATTERNS), re.IGNORECASE)

# Aceitamos letras (incluindo acentos), espaços, hífen e apóstrofo
# Rejeitamos linhas com dígitos ou muitos símbolos estranhos.
valid_name_regex = re.compile(r"^[A-Za-zÀ-ÖØ-öø-ÿ'\-\. ]+$")

# Função para normalizar espaços e remover pontos repetidos
def clean_line(line: str) -> str:
    # remove leading/trailing espaços e múltiplos espaços internos
    s = " ".join(line.strip().split())
    # remove pontos finais soltos (se houver) e espaços redundantes
    s = re.sub(r"\s*\.\s*", " ", s)
    return s.strip()

def likely_name(line: str) -> bool:
    if not line:
        return False
    # rejeita linhas curtas
    if len(line) < 4:
        return False
    # rejeita linhas que contenham dígitos
    if re.search(r"\d", line):
        return False
    # rejeita linhas com muitos símbolos
    if not valid_name_regex.match(line):
        return False
    # prefere nomes com ao menos um espaço (primeiro + sobrenome)
    if " " not in line:
        # ainda permite alguns nomes monônimos curtos? aqui rejeitamos
        return False
    # evita palavras isoladas que são ruído (EX: IPE)
    if len(line.split()) == 1 and len(line) <= 4:
        return False
    return True

def extract_names(text: str):
    names_list = []
    for raw_line in text.splitlines():
        line = raw_line.strip()
        if not line:
            continue
        # pula linhas que batem com blacklist (cabeçalhos)
        if blacklist_regex.search(line):
            continue
        # limpa a linha
        cleaned = clean_line(line)
        if not cleaned:
            continue
        # verifica se parece nome
        if not likely_name(cleaned):
            continue
        # adiciona mesmo se repetido
        names_list.append(cleaned)
    return names_list

def main():
    try:
        with open(INPUT, "r", encoding="utf-8") as f:
            text = f.read()
    except FileNotFoundError:
        print(f"Arquivo '{INPUT}' não encontrado. Cole seu texto nele e execute novamente.")
        return

    names = extract_names(text)

    if not names:
        print("Nenhum nome identificado (verifique o formato do arquivo).")
        return

    # grava o resultado, um por linha, sem espaços extras
    with open(OUTPUT, "w", encoding="utf-8") as f:
        f.write("\n".join(names))

    print(f"{len(names)} nomes extraídos e gravados em '{OUTPUT}'.")

if __name__ == "__main__":
    main()
