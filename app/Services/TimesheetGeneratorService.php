<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TimeRecord;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TimesheetGeneratorService
{
    public function generate(Employee $employee, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $timeRecords = TimeRecord::where('employee_id', $employee->id)
            ->whereBetween('record_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('recorded_at')
            ->get()
            ->groupBy(function($record) {
                return Carbon::parse($record->record_date)->format('Y-m-d');
            });

        $workSchedules = WorkSchedule::where('employee_id', $employee->id)
            ->where(function($query) use ($start, $end) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $start->format('Y-m-d'));
            })
            ->where(function($query) use ($start) {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', $start->format('Y-m-d'));
            })
            ->get()
            ->keyBy('day_of_week');

        $period = CarbonPeriod::create($start, $end);
        
        $dailyRecords = [];
        $calculations = [];

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dayOfWeek = $date->dayOfWeek;
            
            $records = $timeRecords[$dateStr] ?? collect();
            $dailyRecords[$dateStr] = $records;
            
            $expectedSchedule = $workSchedules[$dayOfWeek] ?? null;
            
            $calculations[$dateStr] = $this->calculateHours(
                $records,
                $expectedSchedule,
                $date
            );
        }

        return [
            'employee' => $employee,
            'establishment' => $employee->establishment,
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $end->format('Y-m-d'),
            'dailyRecords' => $dailyRecords,
            'calculations' => $calculations,
        ];
    }

    protected function calculateHours($records, $expectedSchedule, Carbon $date): array
    {
        if ($records->isEmpty()) {
            if ($expectedSchedule && $this->isWorkDay($expectedSchedule)) {
                $expectedMinutes = $this->calculateExpectedMinutes($expectedSchedule);
                return [
                    'worked' => 0,
                    'expected' => $expectedMinutes,
                    'overtime' => 0,
                    'absence' => $expectedMinutes,
                ];
            }
            
            return [
                'worked' => 0,
                'expected' => 0,
                'overtime' => 0,
                'absence' => 0,
            ];
        }

        $workedMinutes = $this->calculateWorkedMinutes($records);
        
        if (!$expectedSchedule) {
            return [
                'worked' => $workedMinutes,
                'expected' => 0,
                'overtime' => 0,
                'absence' => 0,
            ];
        }

        $expectedMinutes = $this->calculateExpectedMinutes($expectedSchedule);
        $difference = $workedMinutes - $expectedMinutes;
        
        return [
            'worked' => $workedMinutes,
            'expected' => $expectedMinutes,
            'overtime' => $difference > 0 ? $difference : 0,
            'absence' => $difference < 0 ? abs($difference) : 0,
        ];
    }

    protected function calculateWorkedMinutes($records): int
    {
        $punches = $records->sortBy('recorded_at')->pluck('recorded_at')->toArray();
        $totalMinutes = 0;
        
        for ($i = 0; $i < count($punches) - 1; $i += 2) {
            $entry = Carbon::parse($punches[$i]);
            $exit = Carbon::parse($punches[$i + 1]);
            
            $totalMinutes += $exit->diffInMinutes($entry);
        }
        
        return $totalMinutes;
    }

    protected function calculateExpectedMinutes($schedule): int
    {
        $totalMinutes = 0;
        
        if ($schedule->entry_1 && $schedule->exit_1) {
            $totalMinutes += $this->minutesBetween($schedule->entry_1, $schedule->exit_1);
        }
        
        if ($schedule->entry_2 && $schedule->exit_2) {
            $totalMinutes += $this->minutesBetween($schedule->entry_2, $schedule->exit_2);
        }
        
        if ($schedule->entry_3 && $schedule->exit_3) {
            $totalMinutes += $this->minutesBetween($schedule->entry_3, $schedule->exit_3);
        }
        
        return $totalMinutes;
    }

    protected function minutesBetween($time1, $time2): int
    {
        // Converte para string se for objeto Carbon/DateTime
        if (is_object($time1)) {
            $time1 = $time1 instanceof Carbon ? $time1->format('H:i:s') : (string) $time1;
        }
        if (is_object($time2)) {
            $time2 = $time2 instanceof Carbon ? $time2->format('H:i:s') : (string) $time2;
        }
        
        // Parse das strings de horário
        $t1 = Carbon::parse($time1);
        $t2 = Carbon::parse($time2);
        
        return $t2->diffInMinutes($t1);
    }

    protected function isWorkDay($schedule): bool
    {
        return $schedule->entry_1 !== null || $schedule->entry_2 !== null || $schedule->entry_3 !== null;
    }
}
