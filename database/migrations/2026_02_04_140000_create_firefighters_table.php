<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('firefighters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardia_id')->nullable()->constrained('guardias')->nullOnDelete();

            $table->string('name');
            $table->string('last_name_paternal')->nullable();
            $table->string('last_name_maternal')->nullable();
            $table->string('rut')->nullable()->unique();

            $table->date('birthdate')->nullable();
            $table->date('admission_date')->nullable();

            $table->string('position_text')->nullable();
            $table->string('portable_number')->nullable();

            $table->boolean('is_driver')->default(false);
            $table->boolean('is_rescue_operator')->default(false);
            $table->boolean('is_trauma_assistant')->default(false);

            $table->boolean('is_shift_leader')->default(false);

            $table->enum('attendance_status', ['constituye', 'reemplazo', 'permiso', 'ausente', 'falta', 'licencia'])->default('constituye');
            $table->boolean('is_titular')->default(true);
            $table->boolean('is_exchange')->default(false);
            $table->boolean('is_penalty')->default(false);

            $table->timestamps();

            $table->index(['guardia_id', 'is_titular']);
            $table->index(['guardia_id', 'attendance_status']);
        });

        $shouldBackfill = Schema::hasColumn('users', 'role');
        if (!$shouldBackfill) {
            return;
        }

        $existing = DB::table('users')
            ->whereIn('role', ['bombero', 'jefe_guardia'])
            ->count();

        if ($existing <= 0) {
            return;
        }

        $now = now();

        DB::transaction(function () use ($now) {
            $rows = DB::table('users')
                ->whereIn('role', ['bombero', 'jefe_guardia'])
                ->get();

            foreach ($rows as $u) {
                DB::table('firefighters')->insert([
                    'guardia_id' => $u->guardia_id,
                    'name' => $u->name,
                    'last_name_paternal' => $u->last_name_paternal,
                    'last_name_maternal' => $u->last_name_maternal,
                    'rut' => $u->rut,
                    'birthdate' => $u->birthdate,
                    'admission_date' => $u->admission_date,
                    'position_text' => $u->position_text,
                    'portable_number' => $u->portable_number,
                    'is_driver' => (bool) ($u->is_driver ?? false),
                    'is_rescue_operator' => (bool) ($u->is_rescue_operator ?? false),
                    'is_trauma_assistant' => (bool) ($u->is_trauma_assistant ?? false),
                    'is_shift_leader' => (bool) ($u->role === 'jefe_guardia') || (bool) ($u->is_shift_leader ?? false),
                    'attendance_status' => $u->attendance_status ?? 'constituye',
                    'is_titular' => (bool) ($u->is_titular ?? true),
                    'is_exchange' => (bool) ($u->is_exchange ?? false),
                    'is_penalty' => (bool) ($u->is_penalty ?? false),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firefighters');
    }
};
