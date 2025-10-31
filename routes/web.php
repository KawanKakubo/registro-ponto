<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    EstablishmentController,
    DepartmentController,
    EmployeeController,
    EmployeeImportController,
    WorkScheduleController,
    AfdImportController,
    TimesheetController,
    AdminController
};
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\FilterController;

// Rotas de autenticação
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Rotas protegidas por autenticação
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Administradores (apenas para admins)
    Route::middleware('admin')->group(function () {
        Route::resource('admins', AdminController::class);
    });

    // Estabelecimentos
    Route::resource('establishments', EstablishmentController::class);

// Departamentos
Route::resource('departments', DepartmentController::class);

// Colaboradores
Route::resource('employees', EmployeeController::class);

// Templates de Jornada
Route::prefix('work-shift-templates')->name('work-shift-templates.')->group(function () {
    Route::get('/', [\App\Http\Controllers\WorkShiftTemplateController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\WorkShiftTemplateController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\WorkShiftTemplateController::class, 'store'])->name('store');
    Route::get('/{template}/edit', [\App\Http\Controllers\WorkShiftTemplateController::class, 'edit'])->name('edit');
    Route::put('/{template}', [\App\Http\Controllers\WorkShiftTemplateController::class, 'update'])->name('update');
    Route::delete('/{template}', [\App\Http\Controllers\WorkShiftTemplateController::class, 'destroy'])->name('destroy');
    Route::get('/bulk-assign', [\App\Http\Controllers\WorkShiftTemplateController::class, 'bulkAssignForm'])->name('bulk-assign');
    Route::post('/bulk-assign', [\App\Http\Controllers\WorkShiftTemplateController::class, 'bulkAssignStore'])->name('bulk-assign.store');
});

// Horários de Trabalho (nested routes)
Route::prefix('employees/{employee}')->name('employees.')->group(function () {
    Route::get('/work-schedules', [WorkScheduleController::class, 'index'])->name('work-schedules.index');
    Route::post('/work-schedules/apply-template', [WorkScheduleController::class, 'applyTemplate'])->name('work-schedules.apply-template');
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
});
