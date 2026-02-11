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
        'es_operador_rescate',
        'es_asistente_trauma',
        'es_jefe_guardia',
        'estado_asistencia',
        'es_titular',
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
        'es_operador_rescate' => 'boolean',
        'es_asistente_trauma' => 'boolean',
        'es_jefe_guardia' => 'boolean',
        'es_titular' => 'boolean',
        'es_refuerzo' => 'boolean',
        'es_cambio' => 'boolean',
        'es_sancion' => 'boolean',
        'fuera_de_servicio' => 'boolean',
    ];

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function legacyUserMap()
    {
        return $this->hasOne(MapaBomberoUsuarioLegacy::class, 'firefighter_id');
    }
}
