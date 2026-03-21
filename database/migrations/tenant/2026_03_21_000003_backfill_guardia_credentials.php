<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $guardias = DB::table('guardias')->get();

        foreach ($guardias as $guardia) {
            $slug = Str::slug($guardia->name) ?: 'guardia-' . $guardia->id;

            // Asegurar slug único
            $baseSlug = $slug;
            $i = 2;
            while (DB::table('guardias')->where('slug', $slug)->where('id', '!=', $guardia->id)->exists()) {
                $slug = $baseSlug . '-' . $i;
                $i++;
            }

            // Buscar el usuario de guardia asociado (creado previamente con rol 'guardia')
            $user = DB::table('users')
                ->where('guardia_id', $guardia->id)
                ->where('role', 'guardia')
                ->first();

            DB::table('guardias')->where('id', $guardia->id)->update([
                'slug' => $slug,
                'user_id' => $user?->id ?? null,
                // Solo si tiene usuario pero sin acceso_username aún, intentar rellenar
                'access_username' => $guardia->access_username ?? ($user?->username ?? null),
            ]);
        }
    }

    public function down(): void
    {
        // No reversible
    }
};
