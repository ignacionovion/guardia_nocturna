<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class VolunteerImportTemplateExport implements WithMultipleSheets
{
    public function __construct(private Collection $specialties)
    {
    }

    public function sheets(): array
    {
        return [
            new VolunteerImportMainSheet(),
            new VolunteerImportHelpSheet($this->specialties),
        ];
    }
}

class VolunteerImportMainSheet implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Plantilla';
    }

    public function headings(): array
    {
        return [
            'rut',
            'nombres',
            'apellido_paterno',
            'apellido_materno',
            'telefono',
            'email',
            'guardia',
            'especialidades',
        ];
    }

    public function array(): array
    {
        return [
            ['12.345.678-9', 'Juan', 'Perez', 'Soto', '+56912345678', 'juan@example.com', '1', 'Hazmat, Gersa'],
            ['11.111.111-1', 'Maria', 'Rojas', 'Diaz', '+56987654321', 'maria@example.com', '2', 'Rescate vehicular'],
        ];
    }
}

class VolunteerImportHelpSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private Collection $specialties)
    {
    }

    public function title(): string
    {
        return 'Ayuda';
    }

    public function headings(): array
    {
        return ['Especialidades válidas activas', 'Icono', 'Color', 'Ejemplo'];
    }

    public function array(): array
    {
        $rows = $this->specialties
            ->map(fn ($specialty) => [
                $specialty->name,
                $specialty->icon,
                $specialty->color,
                $specialty->name,
            ])
            ->values()
            ->all();

        $rows[] = ['', '', '', 'Use coma para separar varias especialidades: Hazmat, Gersa'];

        return $rows;
    }
}
