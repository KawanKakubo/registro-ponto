<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refatoração fundamental: 1 Pessoa (CPF) → N Vínculos (Matrículas)
     */
    public function up(): void
    {
        // PASSO 1: Renomear tabela employees → people (Pessoas)
        Schema::rename('employees', 'people');

        // PASSO 2: Criar tabela employee_registrations (Vínculos/Matrículas)
        Schema::create('employee_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('matricula', 20)->unique();
            $table->foreignId('establishment_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->date('admission_date');
            $table->string('position', 100)->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->timestamps();
            
            $table->index('matricula');
            $table->index(['person_id', 'status']);
        });

        // PASSO 3: Migrar dados existentes
        // Para cada employee atual, criar:
        // 1. Registro na people (mantendo id, cpf, pis, nome)
        // 2. Registro na employee_registrations (matrícula, depto, etc)
        
        $employees = DB::table('people')->get();
        
        foreach ($employees as $employee) {
            // Se o employee tem matrícula, criar um vínculo
            if (!empty($employee->matricula)) {
                DB::table('employee_registrations')->insert([
                    'person_id' => $employee->id,
                    'matricula' => $employee->matricula,
                    'establishment_id' => $employee->establishment_id,
                    'department_id' => $employee->department_id,
                    'admission_date' => $employee->admission_date,
                    'position' => $employee->position,
                    'status' => $employee->status,
                    'created_at' => $employee->created_at,
                    'updated_at' => $employee->updated_at,
                ]);
            }
        }

        // PASSO 4: Remover colunas de vínculo da tabela people
        Schema::table('people', function (Blueprint $table) {
            // PostgreSQL: nomes das constraints são baseados no nome original da tabela (employees)
            $table->dropForeign('employees_establishment_id_foreign');
            $table->dropForeign('employees_department_id_foreign');
            $table->dropColumn([
                'matricula',
                'establishment_id',
                'department_id',
                'admission_date',
                'position',
                'status'
            ]);
        });

        // PASSO 5: Atualizar time_records para referenciar employee_registrations
        // Adicionar nova coluna employee_registration_id
        Schema::table('time_records', function (Blueprint $table) {
            $table->foreignId('employee_registration_id')->nullable()->after('employee_id')
                ->constrained('employee_registrations')->onDelete('cascade');
        });

        // Migrar dados: para cada time_record, encontrar o vínculo correspondente
        // Como anteriormente tínhamos 1 employee = 1 matrícula, podemos fazer o match pelo employee_id
        DB::statement("
            UPDATE time_records tr
            SET employee_registration_id = (
                SELECT er.id 
                FROM employee_registrations er 
                WHERE er.person_id = tr.employee_id 
                LIMIT 1
            )
            WHERE employee_registration_id IS NULL
        ");

        // Remover employee_id de time_records (agora usamos employee_registration_id)
        Schema::table('time_records', function (Blueprint $table) {
            $table->dropForeign('time_records_employee_id_foreign');
            $table->dropColumn('employee_id');
        });

        // Tornar employee_registration_id obrigatório
        Schema::table('time_records', function (Blueprint $table) {
            $table->foreignId('employee_registration_id')->nullable(false)->change();
        });

        // PASSO 6: Atualizar employee_work_shift_assignments para referenciar employee_registrations
        Schema::table('employee_work_shift_assignments', function (Blueprint $table) {
            $table->foreignId('employee_registration_id')->nullable()->after('employee_id')
                ->constrained('employee_registrations')->onDelete('cascade');
        });

        // Migrar dados
        DB::statement("
            UPDATE employee_work_shift_assignments ewsa
            SET employee_registration_id = (
                SELECT er.id 
                FROM employee_registrations er 
                WHERE er.person_id = ewsa.employee_id 
                LIMIT 1
            )
            WHERE employee_registration_id IS NULL
        ");

        // Remover employee_id
        Schema::table('employee_work_shift_assignments', function (Blueprint $table) {
            $table->dropForeign('employee_work_shift_assignments_employee_id_foreign');
            $table->dropColumn('employee_id');
        });

        // Tornar employee_registration_id obrigatório
        Schema::table('employee_work_shift_assignments', function (Blueprint $table) {
            $table->foreignId('employee_registration_id')->nullable(false)->change();
        });

        // PASSO 7: Atualizar work_schedules para referenciar employee_registrations
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->foreignId('employee_registration_id')->nullable()->after('employee_id')
                ->constrained('employee_registrations')->onDelete('cascade');
        });

        // Migrar dados
        DB::statement("
            UPDATE work_schedules ws
            SET employee_registration_id = (
                SELECT er.id 
                FROM employee_registrations er 
                WHERE er.person_id = ws.employee_id 
                LIMIT 1
            )
            WHERE employee_registration_id IS NULL
        ");

        // Remover employee_id
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->dropForeign('work_schedules_employee_id_foreign');
            $table->dropColumn('employee_id');
        });

        // Tornar employee_registration_id obrigatório
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->foreignId('employee_registration_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // REVERTER PASSO 7: work_schedules
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('id');
        });

        DB::statement("
            UPDATE work_schedules ws
            SET employee_id = (
                SELECT er.person_id 
                FROM employee_registrations er 
                WHERE er.id = ws.employee_registration_id
            )
        ");

        Schema::table('work_schedules', function (Blueprint $table) {
            $table->dropForeign(['employee_registration_id']);
            $table->dropColumn('employee_registration_id');
        });

        // REVERTER PASSO 6: employee_work_shift_assignments
        Schema::table('employee_work_shift_assignments', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('id');
        });

        DB::statement("
            UPDATE employee_work_shift_assignments ewsa
            SET employee_id = (
                SELECT er.person_id 
                FROM employee_registrations er 
                WHERE er.id = ewsa.employee_registration_id
            )
        ");

        Schema::table('employee_work_shift_assignments', function (Blueprint $table) {
            $table->dropForeign(['employee_registration_id']);
            $table->dropColumn('employee_registration_id');
        });

        // REVERTER PASSO 5: time_records
        Schema::table('time_records', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('id');
        });

        DB::statement("
            UPDATE time_records tr
            SET employee_id = (
                SELECT er.person_id 
                FROM employee_registrations er 
                WHERE er.id = tr.employee_registration_id
            )
        ");

        Schema::table('time_records', function (Blueprint $table) {
            $table->dropForeign(['employee_registration_id']);
            $table->dropColumn('employee_registration_id');
        });

        // REVERTER PASSO 4: Restaurar colunas em people
        Schema::table('people', function (Blueprint $table) {
            $table->string('matricula', 20)->nullable();
            $table->foreignId('establishment_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->date('admission_date');
            $table->string('position', 100)->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
        });

        // Migrar dados de volta (pegar o primeiro vínculo de cada pessoa)
        DB::statement("
            UPDATE people p
            SET 
                matricula = (SELECT er.matricula FROM employee_registrations er WHERE er.person_id = p.id LIMIT 1),
                establishment_id = (SELECT er.establishment_id FROM employee_registrations er WHERE er.person_id = p.id LIMIT 1),
                department_id = (SELECT er.department_id FROM employee_registrations er WHERE er.person_id = p.id LIMIT 1),
                admission_date = (SELECT er.admission_date FROM employee_registrations er WHERE er.person_id = p.id LIMIT 1),
                position = (SELECT er.position FROM employee_registrations er WHERE er.person_id = p.id LIMIT 1),
                status = (SELECT er.status FROM employee_registrations er WHERE er.person_id = p.id LIMIT 1)
        ");

        // REVERTER PASSO 2: Dropar tabela employee_registrations
        Schema::dropIfExists('employee_registrations');

        // REVERTER PASSO 1: Renomear people → employees
        Schema::rename('people', 'employees');
    }
};
