<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        if (!Schema::hasTable('bomberos')) {
            return;
        }

        // Identificación / contacto
        if (Schema::hasColumn('bomberos', 'name')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN name nombres VARCHAR(255) NOT NULL");
        }
        if (Schema::hasColumn('bomberos', 'last_name_paternal')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN last_name_paternal apellido_paterno VARCHAR(255) NULL");
        }
        if (Schema::hasColumn('bomberos', 'last_name_maternal')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN last_name_maternal apellido_materno VARCHAR(255) NULL");
        }

        if (Schema::hasColumn('bomberos', 'email')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN email correo VARCHAR(255) NULL");
        }

        // Datos importados adicionales
        if (Schema::hasColumn('bomberos', 'registration_number')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN registration_number numero_registro VARCHAR(255) NULL");
        }
        if (Schema::hasColumn('bomberos', 'address_street')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN address_street direccion_calle VARCHAR(255) NULL");
        }
        if (Schema::hasColumn('bomberos', 'address_number')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN address_number direccion_numero VARCHAR(255) NULL");
        }

        // Fechas
        if (Schema::hasColumn('bomberos', 'birthdate')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN birthdate fecha_nacimiento DATE NULL");
        }
        if (Schema::hasColumn('bomberos', 'admission_date')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN admission_date fecha_ingreso DATE NULL");
        }

        // Campos operativos
        if (Schema::hasColumn('bomberos', 'position_text')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN position_text cargo_texto VARCHAR(255) NULL");
        }
        if (Schema::hasColumn('bomberos', 'portable_number')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN portable_number numero_portatil VARCHAR(255) NULL");
        }

        // Especialidades / flags
        if (Schema::hasColumn('bomberos', 'is_driver')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN is_driver es_conductor TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (Schema::hasColumn('bomberos', 'is_rescue_operator')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN is_rescue_operator es_operador_rescate TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (Schema::hasColumn('bomberos', 'is_trauma_assistant')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN is_trauma_assistant es_asistente_trauma TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (Schema::hasColumn('bomberos', 'is_shift_leader')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN is_shift_leader es_jefe_guardia TINYINT(1) NOT NULL DEFAULT 0");
        }

        if (Schema::hasColumn('bomberos', 'attendance_status')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN attendance_status estado_asistencia ENUM('constituye','reemplazo','permiso','ausente','falta','licencia') NOT NULL DEFAULT 'constituye'");
        }

        if (Schema::hasColumn('bomberos', 'is_titular')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN is_titular es_titular TINYINT(1) NOT NULL DEFAULT 1");
        }
        if (Schema::hasColumn('bomberos', 'is_exchange')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN is_exchange es_cambio TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (Schema::hasColumn('bomberos', 'is_penalty')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN is_penalty es_sancion TINYINT(1) NOT NULL DEFAULT 0");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        if (!Schema::hasTable('bomberos')) {
            return;
        }

        if (Schema::hasColumn('bomberos', 'nombres')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN nombres name VARCHAR(255) NOT NULL");
        }
        if (Schema::hasColumn('bomberos', 'apellido_paterno')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN apellido_paterno last_name_paternal VARCHAR(255) NULL");
        }
        if (Schema::hasColumn('bomberos', 'apellido_materno')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN apellido_materno last_name_maternal VARCHAR(255) NULL");
        }

        if (Schema::hasColumn('bomberos', 'correo')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN correo email VARCHAR(255) NULL");
        }

        if (Schema::hasColumn('bomberos', 'numero_registro')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN numero_registro registration_number VARCHAR(255) NULL");
        }
        if (Schema::hasColumn('bomberos', 'direccion_calle')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN direccion_calle address_street VARCHAR(255) NULL");
        }
        if (Schema::hasColumn('bomberos', 'direccion_numero')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN direccion_numero address_number VARCHAR(255) NULL");
        }

        if (Schema::hasColumn('bomberos', 'fecha_nacimiento')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN fecha_nacimiento birthdate DATE NULL");
        }
        if (Schema::hasColumn('bomberos', 'fecha_ingreso')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN fecha_ingreso admission_date DATE NULL");
        }

        if (Schema::hasColumn('bomberos', 'cargo_texto')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN cargo_texto position_text VARCHAR(255) NULL");
        }
        if (Schema::hasColumn('bomberos', 'numero_portatil')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN numero_portatil portable_number VARCHAR(255) NULL");
        }

        if (Schema::hasColumn('bomberos', 'es_conductor')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN es_conductor is_driver TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (Schema::hasColumn('bomberos', 'es_operador_rescate')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN es_operador_rescate is_rescue_operator TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (Schema::hasColumn('bomberos', 'es_asistente_trauma')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN es_asistente_trauma is_trauma_assistant TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (Schema::hasColumn('bomberos', 'es_jefe_guardia')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN es_jefe_guardia is_shift_leader TINYINT(1) NOT NULL DEFAULT 0");
        }

        if (Schema::hasColumn('bomberos', 'estado_asistencia')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN estado_asistencia attendance_status ENUM('constituye','reemplazo','permiso','ausente','falta','licencia') NOT NULL DEFAULT 'constituye'");
        }

        if (Schema::hasColumn('bomberos', 'es_titular')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN es_titular is_titular TINYINT(1) NOT NULL DEFAULT 1");
        }
        if (Schema::hasColumn('bomberos', 'es_cambio')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN es_cambio is_exchange TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (Schema::hasColumn('bomberos', 'es_sancion')) {
            DB::statement("ALTER TABLE bomberos CHANGE COLUMN es_sancion is_penalty TINYINT(1) NOT NULL DEFAULT 0");
        }
    }
};
