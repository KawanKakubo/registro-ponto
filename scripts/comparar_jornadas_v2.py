#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para comparar jornadas de trabalho entre a listagem oficial e o sistema.
Versão 2 - Compara pelos horários extraídos
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

def extract_times(schedule_str):
    """
    Extrai os horários de uma string de jornada.
    Retorna uma tupla com os horários normalizados para comparação.
    """
    if not schedule_str:
        return None
    
    # Remove caracteres especiais e normaliza
    s = schedule_str.upper()
    
    # Encontra todos os padrões de horário (HH:MM ou H:MM ou HH-MM ou H-MM ou só HH)
    # Padrão para horários: 07:30, 7:30, 07-30, 7-30, etc
    time_pattern = r'\b(\d{1,2})[:\-]?(\d{2})?\b'
    matches = re.findall(time_pattern, s)
    
    times = []
    for h, m in matches:
        hour = int(h)
        minute = int(m) if m else 0
        # Ignora números que claramente não são horas (como códigos)
        if 0 <= hour <= 23 and 0 <= minute <= 59:
            times.append(f"{hour:02d}:{minute:02d}")
    
    # Remove duplicatas mantendo ordem
    seen = set()
    unique_times = []
    for t in times:
        if t not in seen:
            seen.add(t)
            unique_times.append(t)
    
    return tuple(unique_times) if unique_times else None

def extract_schedule_code(horario):
    """Extrai o código da jornada do campo HORÁRIO da listagem."""
    if not horario:
        return None, None
    match = re.match(r'^(\d+)\s*-\s*(.+)$', horario.strip())
    if match:
        return match.group(1), horario.strip()
    return None, horario.strip()

def times_match(times1, times2):
    """
    Verifica se dois conjuntos de horários são equivalentes.
    Permite alguma flexibilidade na comparação.
    """
    if times1 is None or times2 is None:
        return False
    
    # Comparação exata primeiro
    if times1 == times2:
        return True
    
    # Se tiverem mesmo número de horários, verifica se são os mesmos
    if len(times1) == len(times2):
        return set(times1) == set(times2)
    
    # Se um tem mais horários que outro, verifica se os principais batem
    # (útil para casos onde um formato tem mais detalhes)
    shorter = times1 if len(times1) < len(times2) else times2
    longer = times2 if len(times1) < len(times2) else times1
    
    # Todos os horários do menor devem estar no maior
    return all(t in longer for t in shorter)

def similarity(a, b):
    """Calcula similaridade entre duas strings."""
    return SequenceMatcher(None, a, b).ratio()

def main():
    # Carregar listagem oficial (CSV do usuário)
    listagem = {}
    print("=" * 80)
    print("COMPARAÇÃO DE JORNADAS DE TRABALHO - v2")
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
            times = extract_times(horario)
            
            if nome:
                listagem[nome] = {
                    'nome_original': row.get('NOME', ''),
                    'pis': pis,
                    'matricula': matricula,
                    'horario': horario,
                    'codigo_jornada': codigo_jornada,
                    'descricao_jornada': descricao_jornada,
                    'times': times
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
            times = extract_times(jornada)
            
            if nome:
                sistema[nome] = {
                    'nome_original': row.get('NOME', ''),
                    'pis': pis,
                    'matricula': matricula,
                    'jornada': jornada,
                    'times': times
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
            times_lista = dados_lista['times']
            times_sistema = dados_sistema['times']
            
            if jornada_sistema == 'SEM JORNADA':
                sem_jornada.append({
                    'nome': dados_lista['nome_original'],
                    'matricula': dados_lista['matricula'] or dados_sistema['matricula'],
                    'pis': dados_lista['pis'],
                    'jornada_esperada': dados_lista['horario'],
                    'jornada_sistema': 'SEM JORNADA',
                    'times_esperados': times_lista,
                    'times_sistema': None
                })
            elif times_match(times_lista, times_sistema):
                # Horários batem!
                ok.append(nome_norm)
            else:
                # Horários diferentes
                jornada_diferente.append({
                    'nome': dados_lista['nome_original'],
                    'matricula': dados_lista['matricula'] or dados_sistema['matricula'],
                    'pis': dados_lista['pis'],
                    'jornada_esperada': dados_lista['horario'],
                    'codigo_esperado': dados_lista['codigo_jornada'],
                    'jornada_sistema': jornada_sistema,
                    'times_esperados': times_lista,
                    'times_sistema': times_sistema
                })
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
            print(f"     LISTAGEM:  {f['jornada_esperada']}")
            print(f"                Horários: {f['times_esperados']}")
            print(f"     SISTEMA:   {f['jornada_sistema']}")
            print(f"                Horários: {f['times_sistema']}")
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
            print(f"     Jornada esperada: {f['jornada_esperada']}")
            if f['similar']:
                print(f"     ⚠️  Possível match: {f['similar']} (similaridade: {f['score']:.0%})")
            print()
    else:
        print("     Nenhum encontrado! ✅")
    
    # 4. Extras no sistema
    print()
    print(f"🔵 FUNCIONÁRIOS NO SISTEMA MAS NÃO NA LISTAGEM ({len(nao_encontrados_listagem)}):")
    print("-" * 80)
    if nao_encontrados_listagem:
        for i, f in enumerate(sorted(nao_encontrados_listagem, key=lambda x: x['nome']), 1):
            print(f"{i:3}. {f['nome']}")
            print(f"     Mat: {f['matricula'] or 'N/A'} | PIS: {f['pis'] or 'N/A'}")
            print(f"     Jornada: {f['jornada_sistema']}")
            print()
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
    print(f"  ✅ Jornadas OK (horários batem):  {len(ok)}")
    print(f"  🔴 Sem jornada no sistema:        {len(sem_jornada)}")
    print(f"  🟠 Jornada diferente:             {len(jornada_diferente)}")
    print(f"  🟡 Não encontrados no sistema:    {len(nao_encontrados_sistema)}")
    print(f"  🔵 Extras no sistema:             {len(nao_encontrados_listagem)}")
    
    # Exportar CSV com problemas
    print()
    print("Exportando relatório CSV...")
    with open('/tmp/relatorio_jornadas_v2.csv', 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(['TIPO_PROBLEMA', 'NOME', 'MATRICULA', 'PIS', 'JORNADA_ESPERADA', 'HORARIOS_ESPERADOS', 'JORNADA_SISTEMA', 'HORARIOS_SISTEMA'])
        
        for item in sem_jornada:
            writer.writerow(['SEM_JORNADA', item['nome'], item['matricula'], item['pis'], 
                           item['jornada_esperada'], str(item['times_esperados']), 'SEM JORNADA', ''])
        
        for item in jornada_diferente:
            writer.writerow(['JORNADA_DIFERENTE', item['nome'], item['matricula'], item['pis'], 
                           item['jornada_esperada'], str(item['times_esperados']), 
                           item['jornada_sistema'], str(item['times_sistema'])])
        
        for item in nao_encontrados_sistema:
            writer.writerow(['NAO_NO_SISTEMA', item['nome'], item['matricula'], item['pis'], 
                           item['jornada_esperada'], '', '', ''])
        
        for item in nao_encontrados_listagem:
            writer.writerow(['EXTRA_NO_SISTEMA', item['nome'], item['matricula'], item['pis'], 
                           '', '', item['jornada_sistema'], ''])
    
    print(f"  → Relatório salvo em /tmp/relatorio_jornadas_v2.csv")
    print()

if __name__ == '__main__':
    main()
