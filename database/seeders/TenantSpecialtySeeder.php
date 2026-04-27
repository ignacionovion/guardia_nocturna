<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'Hazmat', 'icon' => 'biohazard', 'color' => '#7c3aed'],
            ['name' => 'Gersa', 'icon' => 'water', 'color' => '#0284c7'],
            ['name' => 'Incendios forestales', 'icon' => 'tree', 'color' => '#16a34a'],
            ['name' => 'Rescate agreste', 'icon' => 'mountain', 'color' => '#0f766e'],
            ['name' => 'Rescate vehicular', 'icon' => 'car', 'color' => '#ea580c'],
            ['name' => 'Grimp', 'icon' => 'rope', 'color' => '#1d4ed8'],
            ['name' => 'Investigacion de incendios', 'icon' => 'search', 'color' => '#dc2626'],
        ];

        foreach ($defaults as $item) {
            Specialty::query()->firstOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name' => $item['name'],
                    'icon' => $item['icon'],
                    'color' => $item['color'],
                    'active' => true,
                ]
            );
        }
    }
}
