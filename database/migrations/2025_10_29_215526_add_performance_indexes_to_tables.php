<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Índices para tabela employees (colaboradores)
        Schema::table('employees', function (Blueprint $table) {
            $table->index('cpf', 'idx_employees_cpf');
            $table->index('pis_pasep', 'idx_employees_pis');
            $table->index('establishment_id', 'idx_employees_establishment');
            $table->index('department_id', 'idx_employees_department');
            $table->index(['establishment_id', 'department_id'], 'idx_employees_est_dept');
            $table->index('full_name', 'idx_employees_name');
        });

        // Índices para tabela time_records
        Schema::table('time_records', function (Blueprint $table) {
            $table->index('employee_id', 'idx_time_records_employee');
            $table->index('record_date', 'idx_time_records_date');
            $table->index(['employee_id', 'record_date'], 'idx_time_records_emp_date');
            $table->index('recorded_at', 'idx_time_records_recorded_at');
        });

        // Índices para tabela departments
        Schema::table('departments', function (Blueprint $table) {
            $table->index('establishment_id', 'idx_departments_establishment');
            $table->index('name', 'idx_departments_name');
        });

        // Índices para tabela work_schedules
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->index('employee_id', 'idx_work_schedules_employee');
            $table->index('day_of_week', 'idx_work_schedules_day');
            $table->index(['employee_id', 'day_of_week'], 'idx_work_schedules_emp_day');
        });

        // Índices para tabela afd_imports
        Schema::table('afd_imports', function (Blueprint $table) {
            $table->index('status', 'idx_afd_imports_status');
            $table->index('created_at', 'idx_afd_imports_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employees_cpf');
            $table->dropIndex('idx_employees_pis');
            $table->dropIndex('idx_employees_establishment');
            $table->dropIndex('idx_employees_department');
            $table->dropIndex('idx_employees_est_dept');
            $table->dropIndex('idx_employees_name');
        });

        Schema::table('time_records', function (Blueprint $table) {
            $table->dropIndex('idx_time_records_employee');
            $table->dropIndex('idx_time_records_date');
            $table->dropIndex('idx_time_records_emp_date');
            $table->dropIndex('idx_time_records_recorded_at');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex('idx_departments_establishment');
            $table->dropIndex('idx_departments_name');
        });

        Schema::table('work_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_work_schedules_employee');
            $table->dropIndex('idx_work_schedules_day');
            $table->dropIndex('idx_work_schedules_emp_day');
        });

        Schema::table('afd_imports', function (Blueprint $table) {
            $table->dropIndex('idx_afd_imports_status');
            $table->dropIndex('idx_afd_imports_created');
        });
    }
};
