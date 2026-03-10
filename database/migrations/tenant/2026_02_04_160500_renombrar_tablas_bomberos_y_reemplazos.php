<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Soltar FKs que apuntan a firefighters antes de renombrar.
        Schema::table('shift_users', function (Blueprint $table) {
            try {
                $table->dropForeign(['firefighter_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['replaced_firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('staff_events', function (Blueprint $table) {
            try {
                $table->dropForeign(['firefighter_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['replacement_firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('cleaning_assignments', function (Blueprint $table) {
            try {
                $table->dropForeign(['firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('novelties', function (Blueprint $table) {
            try {
                $table->dropForeign(['firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('emergencies', function (Blueprint $table) {
            try {
                $table->dropForeign(['officer_in_charge_firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('firefighter_user_legacy_maps', function (Blueprint $table) {
            try {
                $table->dropForeign(['firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('firefighter_replacements', function (Blueprint $table) {
            try {
                $table->dropForeign(['original_firefighter_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['replacement_firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        // 2) Renombrar tablas del dominio.
        if (Schema::hasTable('firefighters') && !Schema::hasTable('bomberos')) {
            Schema::rename('firefighters', 'bomberos');
        }

        if (Schema::hasTable('firefighter_replacements') && !Schema::hasTable('reemplazos_bomberos')) {
            Schema::rename('firefighter_replacements', 'reemplazos_bomberos');
        }

        if (Schema::hasTable('firefighter_user_legacy_maps') && !Schema::hasTable('mapa_bombero_usuario_legacy')) {
            Schema::rename('firefighter_user_legacy_maps', 'mapa_bombero_usuario_legacy');
        }

        // 3) Re-crear FKs apuntando a bomberos (misma columna, nueva tabla).
        Schema::table('shift_users', function (Blueprint $table) {
            if (Schema::hasColumn('shift_users', 'firefighter_id')) {
                $table->foreign('firefighter_id')->references('id')->on('bomberos')->nullOnDelete();
            }
            if (Schema::hasColumn('shift_users', 'replaced_firefighter_id')) {
                $table->foreign('replaced_firefighter_id')->references('id')->on('bomberos')->nullOnDelete();
            }
        });

        Schema::table('staff_events', function (Blueprint $table) {
            if (Schema::hasColumn('staff_events', 'firefighter_id')) {
                $table->foreign('firefighter_id')->references('id')->on('bomberos')->nullOnDelete();
            }
            if (Schema::hasColumn('staff_events', 'replacement_firefighter_id')) {
                $table->foreign('replacement_firefighter_id')->references('id')->on('bomberos')->nullOnDelete();
            }
        });

        Schema::table('cleaning_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('cleaning_assignments', 'firefighter_id')) {
                $table->foreign('firefighter_id')->references('id')->on('bomberos')->nullOnDelete();
            }
        });

        Schema::table('novelties', function (Blueprint $table) {
            if (Schema::hasColumn('novelties', 'firefighter_id')) {
                $table->foreign('firefighter_id')->references('id')->on('bomberos')->nullOnDelete();
            }
        });

        Schema::table('emergencies', function (Blueprint $table) {
            if (Schema::hasColumn('emergencies', 'officer_in_charge_firefighter_id')) {
                $table->foreign('officer_in_charge_firefighter_id')->references('id')->on('bomberos')->nullOnDelete();
            }
        });

        Schema::table('mapa_bombero_usuario_legacy', function (Blueprint $table) {
            if (Schema::hasColumn('mapa_bombero_usuario_legacy', 'firefighter_id')) {
                $table->foreign('firefighter_id')->references('id')->on('bomberos')->onDelete('cascade');
            }
        });

        Schema::table('reemplazos_bomberos', function (Blueprint $table) {
            if (Schema::hasColumn('reemplazos_bomberos', 'original_firefighter_id')) {
                $table->foreign('original_firefighter_id')->references('id')->on('bomberos')->onDelete('cascade');
            }
            if (Schema::hasColumn('reemplazos_bomberos', 'replacement_firefighter_id')) {
                $table->foreign('replacement_firefighter_id')->references('id')->on('bomberos')->onDelete('cascade');
            }
        });

        // 4) Traducir enum status en reemplazos: active/closed -> activo/cerrado.
        // SQLite no impone enum real, MySQL/MariaDB sí.
        $driver = DB::getDriverName();

        if (Schema::hasTable('reemplazos_bomberos') && Schema::hasColumn('reemplazos_bomberos', 'status')) {
            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement("ALTER TABLE reemplazos_bomberos MODIFY status ENUM('activo','cerrado') NOT NULL DEFAULT 'activo'");
            }

            DB::table('reemplazos_bomberos')->where('status', 'active')->update(['status' => 'activo']);
            DB::table('reemplazos_bomberos')->where('status', 'closed')->update(['status' => 'cerrado']);
        }
    }

    public function down(): void
    {
        // Revertimos solo nombres de tablas y enum (best-effort).
        $driver = DB::getDriverName();

        if (Schema::hasTable('reemplazos_bomberos') && Schema::hasColumn('reemplazos_bomberos', 'status')) {
            DB::table('reemplazos_bomberos')->where('status', 'activo')->update(['status' => 'active']);
            DB::table('reemplazos_bomberos')->where('status', 'cerrado')->update(['status' => 'closed']);

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement("ALTER TABLE reemplazos_bomberos MODIFY status ENUM('active','closed') NOT NULL DEFAULT 'active'");
            }
        }

        // Soltar FKs actuales
        Schema::table('shift_users', function (Blueprint $table) {
            try {
                $table->dropForeign(['firefighter_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['replaced_firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('staff_events', function (Blueprint $table) {
            try {
                $table->dropForeign(['firefighter_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['replacement_firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('cleaning_assignments', function (Blueprint $table) {
            try {
                $table->dropForeign(['firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('novelties', function (Blueprint $table) {
            try {
                $table->dropForeign(['firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('emergencies', function (Blueprint $table) {
            try {
                $table->dropForeign(['officer_in_charge_firefighter_id']);
            } catch (\Throwable $e) {
            }
        });

        if (Schema::hasTable('mapa_bombero_usuario_legacy')) {
            Schema::table('mapa_bombero_usuario_legacy', function (Blueprint $table) {
                try {
                    $table->dropForeign(['firefighter_id']);
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('reemplazos_bomberos')) {
            Schema::table('reemplazos_bomberos', function (Blueprint $table) {
                try {
                    $table->dropForeign(['original_firefighter_id']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropForeign(['replacement_firefighter_id']);
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('bomberos') && !Schema::hasTable('firefighters')) {
            Schema::rename('bomberos', 'firefighters');
        }

        if (Schema::hasTable('reemplazos_bomberos') && !Schema::hasTable('firefighter_replacements')) {
            Schema::rename('reemplazos_bomberos', 'firefighter_replacements');
        }

        if (Schema::hasTable('mapa_bombero_usuario_legacy') && !Schema::hasTable('firefighter_user_legacy_maps')) {
            Schema::rename('mapa_bombero_usuario_legacy', 'firefighter_user_legacy_maps');
        }

        // Re-crear FKs hacia firefighters
        Schema::table('shift_users', function (Blueprint $table) {
            if (Schema::hasColumn('shift_users', 'firefighter_id')) {
                $table->foreign('firefighter_id')->references('id')->on('firefighters')->nullOnDelete();
            }
            if (Schema::hasColumn('shift_users', 'replaced_firefighter_id')) {
                $table->foreign('replaced_firefighter_id')->references('id')->on('firefighters')->nullOnDelete();
            }
        });

        Schema::table('staff_events', function (Blueprint $table) {
            if (Schema::hasColumn('staff_events', 'firefighter_id')) {
                $table->foreign('firefighter_id')->references('id')->on('firefighters')->nullOnDelete();
            }
            if (Schema::hasColumn('staff_events', 'replacement_firefighter_id')) {
                $table->foreign('replacement_firefighter_id')->references('id')->on('firefighters')->nullOnDelete();
            }
        });

        Schema::table('cleaning_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('cleaning_assignments', 'firefighter_id')) {
                $table->foreign('firefighter_id')->references('id')->on('firefighters')->nullOnDelete();
            }
        });

        Schema::table('novelties', function (Blueprint $table) {
            if (Schema::hasColumn('novelties', 'firefighter_id')) {
                $table->foreign('firefighter_id')->references('id')->on('firefighters')->nullOnDelete();
            }
        });

        Schema::table('emergencies', function (Blueprint $table) {
            if (Schema::hasColumn('emergencies', 'officer_in_charge_firefighter_id')) {
                $table->foreign('officer_in_charge_firefighter_id')->references('id')->on('firefighters')->nullOnDelete();
            }
        });

        Schema::table('firefighter_user_legacy_maps', function (Blueprint $table) {
            if (Schema::hasColumn('firefighter_user_legacy_maps', 'firefighter_id')) {
                $table->foreign('firefighter_id')->references('id')->on('firefighters')->onDelete('cascade');
            }
        });

        Schema::table('firefighter_replacements', function (Blueprint $table) {
            if (Schema::hasColumn('firefighter_replacements', 'original_firefighter_id')) {
                $table->foreign('original_firefighter_id')->references('id')->on('firefighters')->onDelete('cascade');
            }
            if (Schema::hasColumn('firefighter_replacements', 'replacement_firefighter_id')) {
                $table->foreign('replacement_firefighter_id')->references('id')->on('firefighters')->onDelete('cascade');
            }
        });
    }
};
