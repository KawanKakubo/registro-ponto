#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para comparar jornadas de trabalho entre a listagem oficial e o sistema.
Versão 4 - Extração de horários corrigida para formato da listagem
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
    name = unicodedata.normalize('NFKD', name).encode('ASCII', 'ignore').decode('ASCII')
    name = ' '.join(name.upper().split())
    return name

def normalize_pis(pis):
    """Normaliza PIS removendo caracteres não numéricos."""
    if not pis:
        return ""
    return re.sub(r'[^0-9]', '', str(pis))

def extract_times_from_listagem(horario):
    """
    Extrai horários da listagem oficial.
    Formato: "155 - NOVO - 08-11:30 E 13-17"
    
    Entende que:
    - "08-11:30" significa "08:00 às 11:30" (entrada-saída)
    - "13-17" significa "13:00 às 17:00"
    - Horário com : é exato (ex: 07:30, 11:30)
    - Horário sem : assume :00 (ex: 08 = 08:00, 13 = 13:00)
    """
    if not horario:
        return None, None
    
    # Extrai código inicial
    codigo = None
    match = re.match(r'^(\d+)\s*-\s*(.+)$', horario.strip())
    if match:
        codigo = match.group(1)
        rest = match.group(2)
    else:
        rest = horario
    
    times = []
    
    # Estratégia: encontrar todos os horários no formato:
    # 1. HH:MM (com minutos explícitos)
    # 2. H ou HH (hora solo, assume :00)
    
    # Primeiro remove o código da jornada se ainda presente
    # e normaliza separadores
    text = rest.upper()
    text = re.sub(r'\s+', ' ', text)
    
    # Encontra padrões de faixas de horário: HH:MM-HH:MM ou HH-HH:MM ou HH:MM-HH ou HH-HH
    # Padrão para capturar um horário individual
    time_pattern = r'(\d{1,2})(?::(\d{2}))?'
    
    # Encontrar todos os blocos de horário
    # Procura por padrões como: "08-11:30", "13-17", "07:30-13", etc.
    # Esses indicam início e fim de um período
    range_pattern = r'(\d{1,2})(?::(\d{2}))?\s*[-AÀ]\s*(\d{1,2})(?::(\d{2}))?'
    
    for m in re.finditer(range_pattern, text):
        h1 = m.group(1)
        m1 = m.group(2) or '00'
        h2 = m.group(3)
        m2 = m.group(4) or '00'
        
        hour1 = int(h1)
        min1 = int(m1)
        hour2 = int(h2)
        min2 = int(m2)
        
        if 0 <= hour1 <= 23 and 0 <= min1 <= 59:
            times.append(f"{hour1:02d}:{min1:02d}")
        if 0 <= hour2 <= 23 and 0 <= min2 <= 59:
            times.append(f"{hour2:02d}:{min2:02d}")
    
    # Se não encontrou faixas, tenta horários soltos
    if not times:
        # Horários com minutos explícitos
        for m in re.finditer(r'(\d{1,2}):(\d{2})', text):
            hour = int(m.group(1))
            minute = int(m.group(2))
            if 0 <= hour <= 23 and 0 <= minute <= 59:
                times.append(f"{hour:02d}:{minute:02d}")
        
        # Se ainda não tem, tenta números soltos (horas)
        if not times:
            for m in re.finditer(r'\b(\d{1,2})\b', text):
                hour = int(m.group(1))
                # Ignora números muito grandes ou que parecem códigos
                if 0 <= hour <= 23:
                    times.append(f"{hour:02d}:00")
    
    # Remove duplicatas mantendo ordem
    seen = set()
    unique_times = []
    for t in times:
        if t not in seen:
            seen.add(t)
            unique_times.append(t)
    
    return codigo, tuple(unique_times) if unique_times else None

def extract_times_from_sistema(jornada):
    """
    Extrai horários do sistema.
    Formato: "NOVO - 08:00 às 11:30 | 13:00 às 17:00"
    """
    if not jornada or jornada == 'SEM JORNADA':
        return None
    
    times = []
    pattern = r'(\d{1,2}):(\d{2})'
    for h, m in re.findall(pattern, jornada):
        hour = int(h)
        minute = int(m)
        if 0 <= hour <= 23 and 0 <= minute <= 59:
            times.append(f"{hour:02d}:{minute:02d}")
    
    # Remove duplicatas
    seen = set()
    unique_times = []
    for t in times:
        if t not in seen:
            seen.add(t)
            unique_times.append(t)
    
    return tuple(unique_times) if unique_times else None

def extract_code_from_sistema(jornada):
    """
    Extrai código da jornada do sistema.
    Formato: "SEC - Jornada 169" ou "SAÚDE - 07:30 às 11:30"
    """
    if not jornada or jornada == 'SEM JORNADA':
        return None
    
    # Procura por "Jornada XXX"
    match = re.search(r'Jornada\s+(\d+)', jornada)
    if match:
        return match.group(1)
    
    return None

def similar(a, b):
    """Calcula similaridade entre duas strings."""
    return SequenceMatcher(None, a, b).ratio()

def compare_times(times1, times2, codigo_listagem=None, codigo_sistema=None):
    """
    Compara duas tuplas de horários.
    Se ambos horários são None, compara por código da jornada.
    Retorna True se são equivalentes.
    """
    # Se ambos têm horários, compara horários
    if times1 is not None and times2 is not None:
        return set(times1) == set(times2)
    
    # Se ambos são None (jornadas especiais sem horário), compara códigos
    if times1 is None and times2 is None:
        if codigo_listagem and codigo_sistema:
            return codigo_listagem == codigo_sistema
        return False
    
    # Um tem horário e outro não - são diferentes
    return False

def main():
    listagem_path = '/home/kawan/Downloads/LISTAGEM DE FUNCIONÁRIOS.csv'
    sistema_path = '/tmp/jornadas_sistema.csv'
    output_path = '/tmp/relatorio_jornadas_final.csv'
    
    # Carrega dados da listagem oficial
    print("Carregando listagem oficial...")
    listagem = {}
    with open(listagem_path, 'r', encoding='utf-8-sig') as f:
        reader = csv.DictReader(f, delimiter=',')
        for row in reader:
            nome = row.get('NOME', '').strip()
            pis = normalize_pis(row.get('Nº PIS/PASEP', '') or row.get('PIS', ''))
            matricula = row.get('Nº IDENTIFICADOR', '') or row.get('MATRÍCULA', '')
            matricula = str(matricula).strip() if matricula else ''
            horario = row.get('HORÁRIO', '').strip()
            
            if nome and pis:
                key = normalize_name(nome)
                listagem[key] = {
                    'nome': nome,
                    'pis': pis,
                    'matricula': matricula,
                    'horario': horario,
                    'nome_norm': key
                }
    
    print(f"  → {len(listagem)} funcionários na listagem")
    
    # Carrega dados do sistema
    print("Carregando dados do sistema...")
    sistema = {}
    with open(sistema_path, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f, delimiter=',')
        for row in reader:
            nome = row.get('NOME', '').strip()
            pis = normalize_pis(row.get('PIS', ''))
            matricula = row.get('MATRICULA', '').strip()
            jornada = row.get('JORNADA_SISTEMA', '').strip()
            
            if nome and pis:
                key = normalize_name(nome)
                sistema[key] = {
                    'nome': nome,
                    'pis': pis,
                    'matricula': matricula,
                    'jornada': jornada if jornada else 'SEM JORNADA',
                    'nome_norm': key
                }
    
    print(f"  → {len(sistema)} funcionários no sistema")
    
    # Cria índice por PIS para busca alternativa
    sistema_por_pis = {v['pis']: k for k, v in sistema.items() if v['pis']}
    listagem_por_pis = {v['pis']: k for k, v in listagem.items() if v['pis']}
    
    # Compara jornadas
    print("\nComparando jornadas...")
    
    ok = []
    sem_jornada = []
    diferente = []
    nao_no_sistema = []
    
    for nome_norm, dados_listagem in listagem.items():
        # Busca por nome normalizado ou PIS
        dados_sistema = None
        
        if nome_norm in sistema:
            dados_sistema = sistema[nome_norm]
        elif dados_listagem['pis'] in sistema_por_pis:
            nome_sistema = sistema_por_pis[dados_listagem['pis']]
            dados_sistema = sistema[nome_sistema]
        else:
            # Tenta busca por similaridade
            melhor_match = None
            melhor_score = 0
            for nome_sis, dados_sis in sistema.items():
                score = similar(nome_norm, nome_sis)
                if score > melhor_score and score >= 0.85:
                    melhor_score = score
                    melhor_match = nome_sis
            
            if melhor_match:
                dados_sistema = sistema[melhor_match]
        
        if not dados_sistema:
            nao_no_sistema.append(dados_listagem)
            continue
        
        # Verifica jornada
        jornada_sistema = dados_sistema['jornada']
        
        if jornada_sistema == 'SEM JORNADA':
            sem_jornada.append({
                'listagem': dados_listagem,
                'sistema': dados_sistema
            })
            continue
        
        # Extrai e compara horários
        codigo_listagem, times_listagem = extract_times_from_listagem(dados_listagem['horario'])
        times_sistema = extract_times_from_sistema(jornada_sistema)
        codigo_sistema = extract_code_from_sistema(jornada_sistema)
        
        if compare_times(times_listagem, times_sistema, codigo_listagem, codigo_sistema):
            ok.append({
                'listagem': dados_listagem,
                'sistema': dados_sistema
            })
        else:
            diferente.append({
                'listagem': dados_listagem,
                'sistema': dados_sistema,
                'codigo_listagem': codigo_listagem,
                'codigo_sistema': codigo_sistema,
                'times_listagem': times_listagem,
                'times_sistema': times_sistema
            })
    
    # Encontra funcionários no sistema que não estão na listagem
    extras_sistema = []
    for nome_norm, dados_sistema in sistema.items():
        if nome_norm not in listagem and dados_sistema['pis'] not in listagem_por_pis:
            # Verifica similaridade
            encontrado = False
            for nome_list in listagem.keys():
                if similar(nome_norm, nome_list) >= 0.85:
                    encontrado = True
                    break
            
            if not encontrado:
                extras_sistema.append(dados_sistema)
    
    # Exibe resultados
    print("\n" + "=" * 80)
    print("RESULTADO DA COMPARAÇÃO")
    print("=" * 80)
    
    print(f"\n✅ JORNADAS OK (horários batem): {len(ok)}")
    
    if sem_jornada:
        print(f"\n🔴 FUNCIONÁRIOS SEM JORNADA NO SISTEMA ({len(sem_jornada)}):")
        print("-" * 80)
        for i, item in enumerate(sem_jornada, 1):
            l = item['listagem']
            s = item['sistema']
            print(f"  {i}. {l['nome']}")
            print(f"     Mat: {l['matricula']} | PIS: {l['pis']}")
            print(f"     Jornada esperada: {l['horario']}")
            print()
    
    if diferente:
        print(f"\n🟠 FUNCIONÁRIOS COM JORNADA DIFERENTE ({len(diferente)}):")
        print("-" * 80)
        for i, item in enumerate(diferente, 1):
            l = item['listagem']
            s = item['sistema']
            print(f"{i}. {l['nome']}")
            print(f"     Mat: {l['matricula']} | PIS: {l['pis']}")
            print(f"     LISTAGEM (código {item['codigo_listagem']}):  {l['horario']}")
            print(f"       Horários extraídos: {item['times_listagem']}")
            print(f"     SISTEMA (código {item['codigo_sistema']}):   {s['jornada']}")
            print(f"       Horários extraídos: {item['times_sistema']}")
            print()
    
    if nao_no_sistema:
        print(f"\n🟡 FUNCIONÁRIOS DA LISTAGEM NÃO ENCONTRADOS NO SISTEMA ({len(nao_no_sistema)}):")
        print("-" * 80)
        for i, l in enumerate(nao_no_sistema, 1):
            print(f"  {i}. {l['nome']}")
            print(f"     Mat: {l['matricula']} | PIS: {l['pis']}")
            print(f"     Jornada esperada: {l['horario']}")
            print()
    
    if extras_sistema:
        print(f"\n🔵 FUNCIONÁRIOS NO SISTEMA MAS NÃO NA LISTAGEM ({len(extras_sistema)}):")
        print("-" * 80)
        for i, s in enumerate(extras_sistema, 1):
            print(f"  {i}. {s['nome']}")
            print(f"     Mat: {s['matricula']} | PIS: {s['pis']}")
            print(f"     Jornada: {s['jornada']}")
            print()
    
    # Resumo
    print("\n" + "=" * 80)
    print("RESUMO")
    print("=" * 80)
    print(f"  Total na listagem oficial:        {len(listagem)}")
    print(f"  Total no sistema (ativos):        {len(sistema)}")
    print()
    print(f"  ✅ Jornadas OK (horários batem):   {len(ok)}")
    print(f"  🔴 Sem jornada no sistema:         {len(sem_jornada)}")
    print(f"  🟠 Jornada diferente:              {len(diferente)}")
    print(f"  🟡 Não encontrados no sistema:     {len(nao_no_sistema)}")
    print(f"  🔵 Extras no sistema:              {len(extras_sistema)}")
    
    # Exporta relatório CSV
    print("\nExportando relatório CSV...")
    with open(output_path, 'w', encoding='utf-8', newline='') as f:
        writer = csv.writer(f)
        writer.writerow(['Status', 'Nome', 'Matrícula', 'PIS', 'Jornada Listagem', 'Jornada Sistema', 'Horários Listagem', 'Horários Sistema'])
        
        for item in ok:
            l = item['listagem']
            s = item['sistema']
            _, times_l = extract_times_from_listagem(l['horario'])
            times_s = extract_times_from_sistema(s['jornada'])
            writer.writerow(['OK', l['nome'], l['matricula'], l['pis'], l['horario'], s['jornada'], str(times_l), str(times_s)])
        
        for item in sem_jornada:
            l = item['listagem']
            s = item['sistema']
            _, times_l = extract_times_from_listagem(l['horario'])
            writer.writerow(['SEM_JORNADA', l['nome'], l['matricula'], l['pis'], l['horario'], s['jornada'], str(times_l), ''])
        
        for item in diferente:
            l = item['listagem']
            s = item['sistema']
            writer.writerow(['DIFERENTE', l['nome'], l['matricula'], l['pis'], l['horario'], s['jornada'], str(item['times_listagem']), str(item['times_sistema'])])
        
        for l in nao_no_sistema:
            _, times_l = extract_times_from_listagem(l['horario'])
            writer.writerow(['NAO_NO_SISTEMA', l['nome'], l['matricula'], l['pis'], l['horario'], '', str(times_l), ''])
        
        for s in extras_sistema:
            times_s = extract_times_from_sistema(s['jornada'])
            writer.writerow(['EXTRA_SISTEMA', s['nome'], s['matricula'], s['pis'], '', s['jornada'], '', str(times_s)])
    
    print(f"  → Relatório salvo em {output_path}")

if __name__ == '__main__':
    main()
