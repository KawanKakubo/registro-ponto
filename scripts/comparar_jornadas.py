#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para comparar jornadas de trabalho entre a listagem oficial e o sistema.
"""

import csv
import re
from collections import defaultdict
from difflib import SequenceMatcher
import unicodedata

def normalize_name(name):
    """Normaliza nome para comparação."""
    if not name:
        return ""
    # Remove acentos
    name = unicodedata.normalize('NFKD', name).encode('ASCII', 'ignore').decode('ASCII')
    # Uppercase e remove espaços extras
    name = ' '.join(name.upper().split())
    return name

def normalize_pis(pis):
    """Normaliza PIS removendo caracteres não numéricos."""
    if not pis:
        return ""
    return re.sub(r'[^0-9]', '', str(pis))

def extract_schedule_code(horario):
    """Extrai o código da jornada do campo HORÁRIO da listagem."""
    if not horario:
        return None, None
    # Padrão: "7 - SAÚDE -07:30-11:30 E 13:00-17:00" -> código "7"
    # ou "223 - SEC - 05:30-10:45" -> código "223"
    match = re.match(r'^(\d+)\s*-\s*(.+)$', horario.strip())
    if match:
        return match.group(1), horario.strip()
    return None, horario.strip()

def similarity(a, b):
    """Calcula similaridade entre duas strings."""
    return SequenceMatcher(None, a, b).ratio()

def main():
    # Carregar listagem oficial (CSV do usuário)
    listagem = {}
    print("=" * 80)
    print("COMPARAÇÃO DE JORNADAS DE TRABALHO")
    print("=" * 80)
    print()
    
    print("Carregando listagem oficial...")
    with open('/home/kawan/Downloads/LISTAGEM DE FUNCIONÁRIOS.csv', 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            nome = normalize_name(row.get('NOME', ''))
            pis = normalize_pis(row.get('Nº PIS/PASEP', ''))
            matricula = row.get('Nº IDENTIFICADOR', '').strip()
            horario = row.get('HORÁRIO', '').strip()
            codigo_jornada, descricao_jornada = extract_schedule_code(horario)
            
            if nome:
                listagem[nome] = {
                    'nome_original': row.get('NOME', ''),
                    'pis': pis,
                    'matricula': matricula,
                    'horario': horario,
                    'codigo_jornada': codigo_jornada,
                    'descricao_jornada': descricao_jornada
                }
    
    print(f"  → {len(listagem)} funcionários na listagem oficial")
    
    # Carregar dados do sistema
    sistema = {}
    print("Carregando dados do sistema...")
    with open('/tmp/jornadas_sistema.csv', 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            nome = normalize_name(row.get('NOME', ''))
            pis = normalize_pis(row.get('PIS', ''))
            matricula = row.get('MATRICULA', '').strip()
            jornada = row.get('JORNADA_SISTEMA', '').strip()
            
            if nome:
                sistema[nome] = {
                    'nome_original': row.get('NOME', ''),
                    'pis': pis,
                    'matricula': matricula,
                    'jornada': jornada
                }
    
    print(f"  → {len(sistema)} funcionários ativos no sistema")
    print()
    
    # Análise
    sem_jornada = []
    jornada_diferente = []
    nao_encontrados_sistema = []
    nao_encontrados_listagem = []
    ok = []
    
    # Para cada funcionário na listagem, verificar no sistema
    for nome_norm, dados_lista in listagem.items():
        if nome_norm in sistema:
            dados_sistema = sistema[nome_norm]
            jornada_sistema = dados_sistema['jornada']
            codigo_lista = dados_lista['codigo_jornada']
            
            if jornada_sistema == 'SEM JORNADA':
                sem_jornada.append({
                    'nome': dados_lista['nome_original'],
                    'matricula': dados_lista['matricula'] or dados_sistema['matricula'],
                    'pis': dados_lista['pis'],
                    'jornada_esperada': dados_lista['horario'],
                    'jornada_sistema': 'SEM JORNADA'
                })
            elif codigo_lista:
                # Verifica se o código da jornada está no nome da jornada do sistema
                # Ex: código "7" deveria estar em "7 - SAÚDE -07:30-11:30..."
                jornada_sistema_codigo = None
                match = re.match(r'^(\d+)\s*-', jornada_sistema)
                if match:
                    jornada_sistema_codigo = match.group(1)
                
                if jornada_sistema_codigo != codigo_lista:
                    jornada_diferente.append({
                        'nome': dados_lista['nome_original'],
                        'matricula': dados_lista['matricula'] or dados_sistema['matricula'],
                        'pis': dados_lista['pis'],
                        'jornada_esperada': dados_lista['horario'],
                        'codigo_esperado': codigo_lista,
                        'jornada_sistema': jornada_sistema,
                        'codigo_sistema': jornada_sistema_codigo
                    })
                else:
                    ok.append(nome_norm)
            else:
                ok.append(nome_norm)
        else:
            # Tentar encontrar por similaridade de nome
            melhor_match = None
            melhor_score = 0
            for nome_sistema in sistema.keys():
                score = similarity(nome_norm, nome_sistema)
                if score > melhor_score and score > 0.85:
                    melhor_score = score
                    melhor_match = nome_sistema
            
            nao_encontrados_sistema.append({
                'nome': dados_lista['nome_original'],
                'matricula': dados_lista['matricula'],
                'pis': dados_lista['pis'],
                'jornada_esperada': dados_lista['horario'],
                'similar': sistema[melhor_match]['nome_original'] if melhor_match else None,
                'score': melhor_score if melhor_match else 0
            })
    
    # Funcionários no sistema mas não na listagem
    for nome_norm, dados_sistema in sistema.items():
        if nome_norm not in listagem:
            nao_encontrados_listagem.append({
                'nome': dados_sistema['nome_original'],
                'matricula': dados_sistema['matricula'],
                'pis': dados_sistema['pis'],
                'jornada_sistema': dados_sistema['jornada']
            })
    
    # Relatório
    print("=" * 80)
    print("RELATÓRIO DE DISCREPÂNCIAS")
    print("=" * 80)
    
    # 1. Sem jornada
    print()
    print(f"🔴 FUNCIONÁRIOS SEM JORNADA NO SISTEMA ({len(sem_jornada)}):")
    print("-" * 80)
    if sem_jornada:
        for i, f in enumerate(sorted(sem_jornada, key=lambda x: x['nome']), 1):
            print(f"{i:3}. {f['nome']}")
            print(f"     Mat: {f['matricula'] or 'N/A'} | PIS: {f['pis'] or 'N/A'}")
            print(f"     Jornada esperada: {f['jornada_esperada']}")
            print()
    else:
        print("     Nenhum encontrado! ✅")
    
    # 2. Jornada diferente
    print()
    print(f"🟠 FUNCIONÁRIOS COM JORNADA DIFERENTE ({len(jornada_diferente)}):")
    print("-" * 80)
    if jornada_diferente:
        for i, f in enumerate(sorted(jornada_diferente, key=lambda x: x['nome']), 1):
            print(f"{i:3}. {f['nome']}")
            print(f"     Mat: {f['matricula'] or 'N/A'} | PIS: {f['pis'] or 'N/A'}")
            print(f"     Esperado (código {f['codigo_esperado']}): {f['jornada_esperada']}")
            print(f"     Sistema  (código {f['codigo_sistema']}): {f['jornada_sistema']}")
            print()
    else:
        print("     Nenhum encontrado! ✅")
    
    # 3. Não encontrados no sistema
    print()
    print(f"🟡 FUNCIONÁRIOS DA LISTAGEM NÃO ENCONTRADOS NO SISTEMA ({len(nao_encontrados_sistema)}):")
    print("-" * 80)
    if nao_encontrados_sistema:
        for i, f in enumerate(sorted(nao_encontrados_sistema, key=lambda x: x['nome']), 1):
            print(f"{i:3}. {f['nome']}")
            print(f"     Mat: {f['matricula'] or 'N/A'} | PIS: {f['pis'] or 'N/A'}")
            if f['similar']:
                print(f"     Possível match: {f['similar']} (similaridade: {f['score']:.0%})")
            print()
    else:
        print("     Nenhum encontrado! ✅")
    
    # 4. Extras no sistema
    print()
    print(f"🔵 FUNCIONÁRIOS NO SISTEMA MAS NÃO NA LISTAGEM ({len(nao_encontrados_listagem)}):")
    print("-" * 80)
    if nao_encontrados_listagem:
        for i, f in enumerate(sorted(nao_encontrados_listagem, key=lambda x: x['nome'])[:20], 1):
            print(f"{i:3}. {f['nome']}")
            print(f"     Mat: {f['matricula'] or 'N/A'} | PIS: {f['pis'] or 'N/A'}")
            print(f"     Jornada: {f['jornada_sistema']}")
            print()
        if len(nao_encontrados_listagem) > 20:
            print(f"     ... e mais {len(nao_encontrados_listagem) - 20} funcionários")
    else:
        print("     Nenhum encontrado! ✅")
    
    # Resumo
    print()
    print("=" * 80)
    print("RESUMO")
    print("=" * 80)
    print(f"  Total na listagem oficial:     {len(listagem)}")
    print(f"  Total no sistema (ativos):     {len(sistema)}")
    print()
    print(f"  ✅ Jornadas OK:                 {len(ok)}")
    print(f"  🔴 Sem jornada no sistema:      {len(sem_jornada)}")
    print(f"  🟠 Jornada diferente:           {len(jornada_diferente)}")
    print(f"  🟡 Não encontrados no sistema:  {len(nao_encontrados_sistema)}")
    print(f"  🔵 Extras no sistema:           {len(nao_encontrados_listagem)}")
    
    # Exportar CSV com problemas
    print()
    print("Exportando relatório CSV...")
    with open('/tmp/relatorio_jornadas.csv', 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(['TIPO_PROBLEMA', 'NOME', 'MATRICULA', 'PIS', 'JORNADA_ESPERADA', 'JORNADA_SISTEMA'])
        
        for item in sem_jornada:
            writer.writerow(['SEM_JORNADA', item['nome'], item['matricula'], item['pis'], item['jornada_esperada'], 'SEM JORNADA'])
        
        for item in jornada_diferente:
            writer.writerow(['JORNADA_DIFERENTE', item['nome'], item['matricula'], item['pis'], item['jornada_esperada'], item['jornada_sistema']])
        
        for item in nao_encontrados_sistema:
            writer.writerow(['NAO_NO_SISTEMA', item['nome'], item['matricula'], item['pis'], item['jornada_esperada'], ''])
    
    print(f"  → Relatório salvo em /tmp/relatorio_jornadas.csv")
    print()

if __name__ == '__main__':
    main()
