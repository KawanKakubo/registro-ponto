#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para extrair informações das jornadas especiais do CSV.
"""

import csv
import re
from collections import defaultdict

def main():
    listagem_path = '/home/kawan/Downloads/LISTAGEM DE FUNCIONÁRIOS.csv'
    
    # Códigos das jornadas especiais sem horários
    codigos_especiais = [
        '169', '250', '210', '61', '16', '215', '126', '213', '116', '249',
        '25', '238', '157', '240', '24', '211', '227', '212', '166', '251',
        '208', '231', '209', '182', '185', '239'
    ]
    
    # Agrupa por código
    jornadas = defaultdict(list)
    
    with open(listagem_path, 'r', encoding='utf-8-sig') as f:
        reader = csv.DictReader(f, delimiter=',')
        for row in reader:
            horario = row.get('HORÁRIO', '').strip()
            if not horario:
                continue
            
            # Extrai código
            match = re.match(r'^(\d+)\s*-\s*(.+)$', horario.strip())
            if match:
                codigo = match.group(1)
                descricao = match.group(2)
                
                if codigo in codigos_especiais:
                    nome = row.get('NOME', '').strip()
                    funcao = row.get('FUNÇÃO', '').strip()
                    jornadas[codigo].append({
                        'codigo': codigo,
                        'descricao': descricao,
                        'nome': nome,
                        'funcao': funcao,
                        'horario_completo': horario
                    })
    
    # Exibe resultados
    print("=" * 80)
    print("JORNADAS ESPECIAIS - DETALHAMENTO")
    print("=" * 80)
    
    for codigo in sorted(codigos_especiais, key=int):
        items = jornadas.get(codigo, [])
        if not items:
            print(f"\n{codigo}: Não encontrado na listagem!")
            continue
        
        print(f"\n{codigo} - {items[0]['descricao']}")
        print(f"  Funcionários: {len(items)}")
        for item in items[:3]:  # Mostra até 3 exemplos
            print(f"    - {item['nome']} ({item['funcao']})")
        if len(items) > 3:
            print(f"    ... e mais {len(items) - 3}")

if __name__ == '__main__':
    main()
