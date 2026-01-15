<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CleaningTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
            ['name' => 'Barrer Sala de Estar', 'description' => 'Barrer y ordenar la sala de estar principal.'],
            ['name' => 'Limpiar Baños', 'description' => 'Limpieza completa de baños de guardia.'],
            ['name' => 'Sacar Basura', 'description' => 'Retirar basura de todos los basureros y depositar en contenedor.'],
            ['name' => 'Ordenar Cocina', 'description' => 'Lavar loza pendiente y limpiar mesones.'],
            ['name' => 'Limpiar Dormitorios', 'description' => 'Barrer dormitorios y verificar orden.'],
        ];

        foreach ($tasks as $task) {
            \App\Models\CleaningTask::create($task);
        }
    }
}
