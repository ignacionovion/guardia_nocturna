<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GuardiaAttendanceRecord extends Model
{
    protected $fillable = [
        'guardia_id', 
        'date', 
        'saved_by_user_id', 
        'saved_at',
        'is_corrected',
        'corrected_at',
        'corrected_by_user_id',
        'corrections_log'
    ];

    protected $casts = [
        'date' => 'date',
        'saved_at' => 'datetime',
        'is_corrected' => 'boolean',
        'corrected_at' => 'datetime',
        'corrections_log' => 'array',
    ];

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function savedBy()
    {
        return $this->belongsTo(User::class, 'saved_by_user_id');
    }

    public function correctedBy()
    {
        return $this->belongsTo(User::class, 'corrected_by_user_id');
    }

    /**
     * Verifica si la asistencia aún puede editarse (hasta las 07:00 del día siguiente)
     */
    public function canEdit(): bool
    {
        if (!$this->saved_at) {
            return true;
        }

        // Crear fecha límite: día del registro a las 07:00 del día siguiente
        $limitDate = $this->date->copy()->addDay()->setTime(7, 0, 0);
        
        return Carbon::now()->lt($limitDate);
    }

    /**
     * Agrega una entrada al log de correcciones
     */
    public function addCorrectionLog(string $action, array $details, int $userId): void
    {
        $log = $this->corrections_log ?? [];
        
        $log[] = [
            'timestamp' => Carbon::now()->toIso8601String(),
            'action' => $action,
            'details' => $details,
            'user_id' => $userId,
        ];
        
        $this->update([
            'corrections_log' => $log,
            'is_corrected' => true,
            'corrected_at' => Carbon::now(),
            'corrected_by_user_id' => $userId,
        ]);
    }
}
