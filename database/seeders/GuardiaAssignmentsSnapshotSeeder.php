<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Bombero;
use App\Models\Guardia;

class GuardiaAssignmentsSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('database/seeders/data/guardia_assignments_snapshot.json');

        if (!File::exists($path)) {
            return;
        }

        $raw = File::get($path);
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return;
        }

        $items = $payload['assignments'] ?? [];
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $guardiaName = trim((string) ($item['guardia_name'] ?? ''));
            $ruts = $item['bomberos_rut'] ?? [];

            if ($guardiaName === '' || !is_array($ruts) || empty($ruts)) {
                continue;
            }

            $guardia = Guardia::updateOrCreate(['name' => $guardiaName], []);

            foreach ($ruts as $rut) {
                $rut = trim((string) $rut);
                if ($rut === '') {
                    continue;
                }

                $bombero = Bombero::query()->where('rut', $rut)->first();
                if (!$bombero) {
                    continue;
                }

                $bombero->update([
                    'guardia_id' => $guardia->id,
                    'es_titular' => true,
                ]);
            }
        }
    }
}
