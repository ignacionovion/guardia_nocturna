<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class VolunteerImportTemplateExport implements WithMultipleSheets
{
    public function __construct(
        private Collection $specialties,
        private Collection $guardias,
    )
    {
    }

    public function sheets(): array
    {
        return [
            new VolunteerImportMainSheet(),
            new VolunteerImportHelpSheet($this->specialties, $this->guardias),
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
            'fecha_nacimiento',
            'telefono',
            'email',
            'direccion',
            'numero_registro',
            'guardia',
            'cargo',
            'estado',
            'especialidades',
            'numero_radial',
        ];
    }

    public function array(): array
    {
        return [
            [
                '12.345.678-9',
                'Juan',
                'Perez',
                'Soto',
                '1990-04-15',
                '+56912345678',
                'juan@example.com',
                'Av. Central 1234',
                '611',
                'Guardia 1',
                'teniente 1',
                'constituye',
                'Hazmat, Gersa',
                'R-12',
            ],
            [
                '11.111.111-1',
                'Maria',
                'Rojas',
                'Diaz',
                '1994-09-20',
                '+56987654321',
                'maria@example.com',
                'Pasaje Sur 456',
                '734',
                'Guardia 2',
                'bombero',
                'reemplazo',
                'Rescate vehicular',
                'R-7',
            ],
        ];
    }
}

class VolunteerImportHelpSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        private Collection $specialties,
        private Collection $guardias,
    )
    {
    }

    public function title(): string
    {
        return 'Ayuda';
    }

    public function headings(): array
    {
        return ['Seccion', 'Campo', 'Detalle', 'Ejemplo'];
    }

    public function array(): array
    {
        $rows = [
            ['Obligatorio', 'rut', 'RUT unico por voluntario', '12.345.678-9'],
            ['Obligatorio', 'nombres', 'Nombre(s) del voluntario', 'Juan Carlos'],
            ['Obligatorio', 'apellido_paterno', 'Apellido paterno', 'Perez'],
            ['Obligatorio', 'apellido_materno', 'Apellido materno', 'Soto'],
            ['Obligatorio', 'cargo', 'Debe ser uno de los cargos permitidos', 'bombero'],
            ['Opcional', 'fecha_nacimiento', 'Formato recomendado AAAA-MM-DD', '1990-04-15'],
            ['Opcional', 'especialidades', 'Separadas por coma. Deben existir y estar activas.', 'Hazmat, Gersa'],
            ['Opcional', 'estado', 'Valores admitidos: constituye, reemplazo, permiso, ausente, falta, licencia', 'constituye'],
            ['Compatibilidad', 'conductor / operador_rescate / asistente_trauma', 'Columnas legacy aceptadas temporalmente; se convierten a especialidades.', 'true'],
            ['Nota', 'Plantillas antiguas', 'Si vienen columnas antiguas extra, se ignoran sin error.', ''],
            ['', '', '', ''],
            ['Guardias', 'guardia', 'Se puede usar ID o nombre exacto de guardia', 'Guardia 1'],
        ];

        foreach ($this->guardias as $guardia) {
            $rows[] = ['Guardias validas', 'guardia', (string) $guardia->name, (string) $guardia->id];
        }

        $rows[] = ['', '', '', ''];
        $rows[] = ['Especialidades', 'especialidades', 'Solo activas del tenant', ''];

        foreach ($this->specialties as $specialty) {
            $rows[] = ['Especialidad valida', 'especialidades', (string) $specialty->name, (string) $specialty->name];
        }

        $rows[] = ['', '', '', ''];
        $rows[] = ['Cargos validos', 'cargo', 'Valores permitidos (insensible a mayúsculas)', ''];
        $rows[] = ['Cargo', 'cargo', 'bombero', 'bombero'];
        $rows[] = ['Cargo', 'cargo', 'capitan', 'capitan'];
        $rows[] = ['Cargo', 'cargo', 'teniente 1', 'teniente 1'];
        $rows[] = ['Cargo', 'cargo', 'director', 'director'];
        $rows[] = ['Cargo', 'cargo', 'secretario', 'secretario'];
        $rows[] = ['Cargo', 'cargo', 'tesorero', 'tesorero'];
        $rows[] = ['Cargo', 'cargo', 'teniente 2', 'teniente 2'];
        $rows[] = ['Cargo', 'cargo', 'teniente 3', 'teniente 3'];
        $rows[] = ['Cargo', 'cargo', 'teniente 4', 'teniente 4'];
        $rows[] = ['Cargo', 'cargo', 'ayudante', 'ayudante'];
        $rows[] = ['Cargo', 'cargo', 'ayudante 1', 'ayudante 1'];
        $rows[] = ['Cargo', 'cargo', 'ayudante 2', 'ayudante 2'];
        $rows[] = ['Cargo', 'cargo', 'ayudante 3', 'ayudante 3'];
        $rows[] = ['Cargo', 'cargo', 'pro secretario', 'pro secretario'];
        $rows[] = ['Cargo', 'cargo', 'pro tesorero', 'pro tesorero'];
        $rows[] = ['Cargo', 'cargo', 'administrativo', 'administrativo'];

        return $rows;
    }
}
