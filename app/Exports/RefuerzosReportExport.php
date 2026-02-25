<?php

namespace App\Exports;

use App\Models\ShiftUser;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RefuerzosReportExport implements FromCollection, WithHeadings, WithStyles, WithMapping
{
    private Carbon $from;
    private Carbon $to;
    private ?int $guardiaId;

    public function __construct(Carbon $from, Carbon $to, ?int $guardiaId = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->guardiaId = $guardiaId;
    }

    public function collection()
    {
        $query = ShiftUser::query()
            ->whereBetween('start_time', [$this->from, $this->to])
            ->where(function($q) {
                $q->where('assignment_type', 'refuerzo')
                  ->orWhereHas('firefighter', fn($q2) => $q2->where('es_refuerzo', true));
            })
            ->whereNotNull('firefighter_id')
            ->with(['firefighter.guardia', 'shift']);

        if ($this->guardiaId) {
            $query->whereHas('firefighter', fn($q) => $q->where('guardia_id', $this->guardiaId));
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Día Semana',
            'Guardia',
            'Voluntario',
            'RUT',
            'Turno',
            'Hora Inicio',
            'Hora Fin',
            'Estado Asistencia',
            'Tipo',
        ];
    }

    public function map($row): array
    {
        return [
            $row->start_time->format('d/m/Y'),
            $row->start_time->locale('es')->dayName,
            $row->firefighter?->guardia?->name ?? 'N/A',
            $row->firefighter?->full_name ?? 'Sin nombre',
            $row->firefighter?->rut ?? '',
            $row->shift?->name ?? 'N/A',
            $row->start_time->format('H:i'),
            $row->end_time?->format('H:i') ?? 'N/A',
            ucfirst($row->attendance_status ?? 'pendiente'),
            $row->assignment_type ?? 'refuerzo',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0EA5E9']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        // Auto-width columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoWidth(true);
        }

        // Row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
