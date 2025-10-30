<?php

namespace App\Services;

use DateTime;

class RotatingShiftCalculatorService
{
    /**
     * Verifica se uma data específica é dia de trabalho baseado na escala rotativa
     *
     * @param DateTime $targetDate Data a verificar
     * @param DateTime $cycleStartDate Data de início do ciclo
     * @param int $workDays Número de dias de trabalho no ciclo
     * @param int $restDays Número de dias de descanso no ciclo
     * @return bool
     */
    public function isWorkingDay(DateTime $targetDate, DateTime $cycleStartDate, int $workDays, int $restDays): bool
    {
        // Calcula quantos dias se passaram desde o início do ciclo
        $daysSinceStart = $targetDate->diff($cycleStartDate)->days;
        
        // Tamanho do ciclo completo
        $cycleLength = $workDays + $restDays;
        
        // Posição no ciclo atual (0 a cycleLength-1)
        $positionInCycle = $daysSinceStart % $cycleLength;
        
        // Se a posição é menor que workDays, é dia de trabalho
        return $positionInCycle < $workDays;
    }

    /**
     * Retorna todos os dias de trabalho em um intervalo de datas
     *
     * @param DateTime $startDate Data inicial
     * @param DateTime $endDate Data final
     * @param DateTime $cycleStartDate Data de início do ciclo
     * @param int $workDays Número de dias de trabalho no ciclo
     * @param int $restDays Número de dias de descanso no ciclo
     * @return array Array de objetos DateTime representando os dias de trabalho
     */
    public function getWorkingDaysInRange(
        DateTime $startDate, 
        DateTime $endDate, 
        DateTime $cycleStartDate, 
        int $workDays, 
        int $restDays
    ): array {
        $workingDays = [];
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            if ($this->isWorkingDay($currentDate, $cycleStartDate, $workDays, $restDays)) {
                $workingDays[] = clone $currentDate;
            }
            $currentDate->modify('+1 day');
        }

        return $workingDays;
    }

    /**
     * Retorna o próximo dia de trabalho após uma data específica
     *
     * @param DateTime $currentDate Data atual
     * @param DateTime $cycleStartDate Data de início do ciclo
     * @param int $workDays Número de dias de trabalho no ciclo
     * @param int $restDays Número de dias de descanso no ciclo
     * @param int $maxDaysToCheck Máximo de dias para procurar (padrão: 30)
     * @return DateTime|null
     */
    public function getNextWorkDay(
        DateTime $currentDate, 
        DateTime $cycleStartDate, 
        int $workDays, 
        int $restDays,
        int $maxDaysToCheck = 30
    ): ?DateTime {
        $checkDate = clone $currentDate;
        $checkDate->modify('+1 day');

        for ($i = 0; $i < $maxDaysToCheck; $i++) {
            if ($this->isWorkingDay($checkDate, $cycleStartDate, $workDays, $restDays)) {
                return $checkDate;
            }
            $checkDate->modify('+1 day');
        }

        return null;
    }

    /**
     * Retorna o próximo dia de folga após uma data específica
     *
     * @param DateTime $currentDate Data atual
     * @param DateTime $cycleStartDate Data de início do ciclo
     * @param int $workDays Número de dias de trabalho no ciclo
     * @param int $restDays Número de dias de descanso no ciclo
     * @param int $maxDaysToCheck Máximo de dias para procurar (padrão: 30)
     * @return DateTime|null
     */
    public function getNextRestDay(
        DateTime $currentDate, 
        DateTime $cycleStartDate, 
        int $workDays, 
        int $restDays,
        int $maxDaysToCheck = 30
    ): ?DateTime {
        $checkDate = clone $currentDate;
        $checkDate->modify('+1 day');

        for ($i = 0; $i < $maxDaysToCheck; $i++) {
            if (!$this->isWorkingDay($checkDate, $cycleStartDate, $workDays, $restDays)) {
                return $checkDate;
            }
            $checkDate->modify('+1 day');
        }

        return null;
    }

    /**
     * Calcula quantos dias de trabalho existem em um mês específico
     *
     * @param int $year Ano
     * @param int $month Mês (1-12)
     * @param DateTime $cycleStartDate Data de início do ciclo
     * @param int $workDays Número de dias de trabalho no ciclo
     * @param int $restDays Número de dias de descanso no ciclo
     * @return int
     */
    public function getWorkingDaysInMonth(
        int $year, 
        int $month, 
        DateTime $cycleStartDate, 
        int $workDays, 
        int $restDays
    ): int {
        $startDate = new DateTime("$year-$month-01");
        $endDate = new DateTime($startDate->format('Y-m-t'));

        $workingDays = $this->getWorkingDaysInRange($startDate, $endDate, $cycleStartDate, $workDays, $restDays);

        return count($workingDays);
    }
}
