<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bombero extends Model
{
    protected $table = 'bomberos';

    protected $fillable = [
        'guardia_id',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'rut',
        'correo',
        'photo_path',
        'numero_registro',
        'direccion_calle',
        'direccion_numero',
        'fecha_nacimiento',
        'fecha_ingreso',
        'cargo_texto',
        'numero_portatil',
        'es_conductor',
        'conductor_carros_bomba',
        'es_operador_rescate',
        'es_asistente_trauma',
        'es_jefe_guardia',
        'estado_asistencia',
        'es_titular',
        'es_permanente',
        'es_refuerzo',
        'refuerzo_guardia_anterior_id',
        'es_cambio',
        'es_sancion',
        'fuera_de_servicio',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'es_conductor' => 'boolean',
        'conductor_carros_bomba' => 'boolean',
        'es_operador_rescate' => 'boolean',
        'es_asistente_trauma' => 'boolean',
        'es_jefe_guardia' => 'boolean',
        'es_titular' => 'boolean',
        'es_permanente' => 'boolean',
        'es_refuerzo' => 'boolean',
        'es_cambio' => 'boolean',
        'es_sancion' => 'boolean',
        'fuera_de_servicio' => 'boolean',
    ];

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function shiftUsers()
    {
        return $this->hasMany(ShiftUser::class, 'firefighter_id');
    }

    public function legacyUserMap()
    {
        return $this->hasOne(MapaBomberoUsuarioLegacy::class, 'firefighter_id');
    }
    
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombres . ' ' . $this->apellido_paterno . ' ' . ($this->apellido_materno ?? ''));
    }
    
    public function getServiceLabelAttribute()
    {
        if (!$this->fecha_ingreso) {
            return '—';
        }
        
        $diff = now()->diff($this->fecha_ingreso);
        $parts = [];
        
        if ($diff->y > 0) {
            $parts[] = $diff->y . ' ' . ($diff->y == 1 ? 'año' : 'años');
        }
        
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' ' . ($diff->m == 1 ? 'mes' : 'meses');
        }
        
        if (count($parts) > 0) {
            return implode(' ', $parts);
        }
        
        return 'Nuevo';
    }
}
