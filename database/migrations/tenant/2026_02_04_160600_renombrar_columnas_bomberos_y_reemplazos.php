<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Esta migración está pensada para MySQL/MariaDB.
        $driver = DB::getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        // 1) reemplazos_bomberos
        if (Schema::hasTable('reemplazos_bomberos')) {
            // Soltar FKs antes de renombrar columnas.
            try {
                DB::statement('ALTER TABLE reemplazos_bomberos DROP FOREIGN KEY reemplazos_bomberos_original_firefighter_id_foreign');
            } catch (\Throwable $e) {
            }
            try {
                DB::statement('ALTER TABLE reemplazos_bomberos DROP FOREIGN KEY reemplazos_bomberos_replacement_firefighter_id_foreign');
            } catch (\Throwable $e) {
            }

            if (Schema::hasColumn('reemplazos_bomberos', 'original_firefighter_id')) {
                DB::statement('ALTER TABLE reemplazos_bomberos CHANGE COLUMN original_firefighter_id bombero_titular_id BIGINT UNSIGNED NOT NULL');
            }
            if (Schema::hasColumn('reemplazos_bomberos', 'replacement_firefighter_id')) {
                DB::statement('ALTER TABLE reemplazos_bomberos CHANGE COLUMN replacement_firefighter_id bombero_reemplazante_id BIGINT UNSIGNED NOT NULL');
            }

            if (Schema::hasColumn('reemplazos_bomberos', 'starts_at')) {
                DB::statement('ALTER TABLE reemplazos_bomberos CHANGE COLUMN starts_at inicio TIMESTAMP NOT NULL');
            }
            if (Schema::hasColumn('reemplazos_bomberos', 'ends_at')) {
                DB::statement('ALTER TABLE reemplazos_bomberos CHANGE COLUMN ends_at fin TIMESTAMP NULL');
            }

            if (Schema::hasColumn('reemplazos_bomberos', 'status')) {
                DB::statement("ALTER TABLE reemplazos_bomberos CHANGE COLUMN status estado ENUM('activo','cerrado') NOT NULL DEFAULT 'activo'");
            }
            if (Schema::hasColumn('reemplazos_bomberos', 'notes')) {
                DB::statement('ALTER TABLE reemplazos_bomberos CHANGE COLUMN notes notas TEXT NULL');
            }

            // Re-crear FKs a bomberos
            DB::statement('ALTER TABLE reemplazos_bomberos ADD CONSTRAINT reemplazos_bomberos_bombero_titular_id_foreign FOREIGN KEY (bombero_titular_id) REFERENCES bomberos(id) ON DELETE CASCADE');
            DB::statement('ALTER TABLE reemplazos_bomberos ADD CONSTRAINT reemplazos_bomberos_bombero_reemplazante_id_foreign FOREIGN KEY (bombero_reemplazante_id) REFERENCES bomberos(id) ON DELETE CASCADE');

            // Ajustar índices si aún existen con los nombres antiguos
            try {
                DB::statement('ALTER TABLE reemplazos_bomberos DROP INDEX reemplazos_bomberos_original_firefighter_id_status_index');
            } catch (\Throwable $e) {
            }
            try {
                DB::statement('ALTER TABLE reemplazos_bomberos DROP INDEX reemplazos_bomberos_replacement_firefighter_id_status_index');
            } catch (\Throwable $e) {
            }

            // Crear índices con columnas nuevas
            try {
                DB::statement('CREATE INDEX reemplazos_bomberos_bombero_titular_id_estado_index ON reemplazos_bomberos (bombero_titular_id, estado)');
            } catch (\Throwable $e) {
            }
            try {
                DB::statement('CREATE INDEX reemplazos_bomberos_bombero_reemplazante_id_estado_index ON reemplazos_bomberos (bombero_reemplazante_id, estado)');
            } catch (\Throwable $e) {
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        // Revertimos reemplazos primero.
        if (Schema::hasTable('reemplazos_bomberos')) {
            try {
                DB::statement('ALTER TABLE reemplazos_bomberos DROP FOREIGN KEY reemplazos_bomberos_bombero_titular_id_foreign');
            } catch (\Throwable $e) {
            }
            try {
                DB::statement('ALTER TABLE reemplazos_bomberos DROP FOREIGN KEY reemplazos_bomberos_bombero_reemplazante_id_foreign');
            } catch (\Throwable $e) {
            }

            if (Schema::hasColumn('reemplazos_bomberos', 'bombero_titular_id')) {
                DB::statement('ALTER TABLE reemplazos_bomberos CHANGE COLUMN bombero_titular_id original_firefighter_id BIGINT UNSIGNED NOT NULL');
            }
            if (Schema::hasColumn('reemplazos_bomberos', 'bombero_reemplazante_id')) {
                DB::statement('ALTER TABLE reemplazos_bomberos CHANGE COLUMN bombero_reemplazante_id replacement_firefighter_id BIGINT UNSIGNED NOT NULL');
            }

            if (Schema::hasColumn('reemplazos_bomberos', 'inicio')) {
                DB::statement('ALTER TABLE reemplazos_bomberos CHANGE COLUMN inicio starts_at TIMESTAMP NOT NULL');
            }
            if (Schema::hasColumn('reemplazos_bomberos', 'fin')) {
                DB::statement('ALTER TABLE reemplazos_bomberos CHANGE COLUMN fin ends_at TIMESTAMP NULL');
            }

            if (Schema::hasColumn('reemplazos_bomberos', 'estado')) {
                DB::statement("ALTER TABLE reemplazos_bomberos CHANGE COLUMN estado status ENUM('activo','cerrado') NOT NULL DEFAULT 'activo'");
            }
            if (Schema::hasColumn('reemplazos_bomberos', 'notas')) {
                DB::statement('ALTER TABLE reemplazos_bomberos CHANGE COLUMN notas notes TEXT NULL');
            }

            // Intentar restaurar FKs con nombres antiguos (best-effort)
            try {
                DB::statement('ALTER TABLE reemplazos_bomberos ADD CONSTRAINT reemplazos_bomberos_original_firefighter_id_foreign FOREIGN KEY (original_firefighter_id) REFERENCES bomberos(id) ON DELETE CASCADE');
            } catch (\Throwable $e) {
            }
            try {
                DB::statement('ALTER TABLE reemplazos_bomberos ADD CONSTRAINT reemplazos_bomberos_replacement_firefighter_id_foreign FOREIGN KEY (replacement_firefighter_id) REFERENCES bomberos(id) ON DELETE CASCADE');
            } catch (\Throwable $e) {
            }
        }

        // No revertimos cambios de bomberos porque este migration no los modifica.
    }
};
