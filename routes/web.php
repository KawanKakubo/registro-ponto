<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    EstablishmentController,
    DepartmentController,
    EmployeeController,
    EmployeeImportController,
    WorkScheduleController,
    AfdImportController,
    TimesheetController
};
use App\Http\Controllers\Api\FilterController;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Estabelecimentos
Route::resource('establishments', EstablishmentController::class);

// Departamentos
Route::resource('departments', DepartmentController::class);

// Colaboradores
Route::resource('employees', EmployeeController::class);

// Horários de Trabalho (nested routes)
Route::prefix('employees/{employee}')->name('employees.')->group(function () {
    Route::get('/work-schedules', [WorkScheduleController::class, 'index'])->name('work-schedules.index');
    Route::get('/work-schedules/create', [WorkScheduleController::class, 'create'])->name('work-schedules.create');
    Route::post('/work-schedules', [WorkScheduleController::class, 'store'])->name('work-schedules.store');
    Route::get('/work-schedules/{workSchedule}/edit', [WorkScheduleController::class, 'edit'])->name('work-schedules.edit');
    Route::put('/work-schedules/{workSchedule}', [WorkScheduleController::class, 'update'])->name('work-schedules.update');
    Route::delete('/work-schedules/{workSchedule}', [WorkScheduleController::class, 'destroy'])->name('work-schedules.destroy');
});

// Importação AFD
Route::prefix('afd-imports')->group(function () {
    Route::get('/', [AfdImportController::class, 'index'])->name('afd-imports.index');
    Route::get('/create', [AfdImportController::class, 'create'])->name('afd-imports.create');
    Route::post('/', [AfdImportController::class, 'store'])->name('afd-imports.store');
    Route::get('/{afdImport}', [AfdImportController::class, 'show'])->name('afd-imports.show');
});

// Importação de Colaboradores (CSV)
Route::prefix('employee-imports')->group(function () {
    Route::get('/', [EmployeeImportController::class, 'index'])->name('employee-imports.index');
    Route::get('/create', [EmployeeImportController::class, 'create'])->name('employee-imports.create');
    Route::get('/template', [EmployeeImportController::class, 'downloadTemplate'])->name('employee-imports.template');
    Route::post('/upload', [EmployeeImportController::class, 'upload'])->name('employee-imports.upload');
    Route::post('/{import}/process', [EmployeeImportController::class, 'process'])->name('employee-imports.process');
    Route::get('/{import}', [EmployeeImportController::class, 'show'])->name('employee-imports.show');
});

// Cartão de Ponto
Route::prefix('timesheets')->group(function () {
    Route::get('/', [TimesheetController::class, 'index'])->name('timesheets.index');
    Route::post('/generate', [TimesheetController::class, 'generate'])->name('timesheets.generate');
    Route::get('/show', [TimesheetController::class, 'show'])->name('timesheets.show');
});

// API para filtros em cascata
Route::prefix('api')->group(function () {
    Route::get('/establishments', [FilterController::class, 'getEstablishments']);
    Route::get('/departments', [FilterController::class, 'getDepartmentsByEstablishment']);
    Route::get('/employees/search', [FilterController::class, 'searchEmployees']);
});
