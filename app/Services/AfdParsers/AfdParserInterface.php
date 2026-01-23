<?php

namespace App\Services\AfdParsers;

use App\Models\AfdImport;

/**
 * Interface para parsers de arquivos AFD
 * 
 * Define o contrato que todos os parsers específicos devem implementar
 */
interface AfdParserInterface
{
    /**
     * Processa o arquivo AFD
     *
     * @param string $filePath Caminho completo do arquivo
     * @param AfdImport $afdImport Registro de importação
     * @return array Resultado do processamento
     */
    public function parse(string $filePath, AfdImport $afdImport): array;

    /**
     * Detecta se este parser é compatível com o arquivo
     *
     * @param string $filePath Caminho do arquivo
     * @return bool True se o parser pode processar o arquivo
     */
    public function canParse(string $filePath): bool;

    /**
     * Retorna o nome do formato/modelo de relógio
     *
     * @return string
     */
    public function getFormatName(): string;

    /**
     * Retorna uma descrição do formato
     *
     * @return string
     */
    public function getFormatDescription(): string;
}
