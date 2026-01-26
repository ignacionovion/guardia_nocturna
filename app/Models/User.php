<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthdate',
        'role',
        'age',
        'years_of_service',
        'guardia_id',
        'is_driver',
        'last_name_paternal',
        'last_name_maternal',
        'rut',
        'company',
        'registration_number',
        'company_registration_number',
        'call_code',
        'position_text',
        'phone',
        'gender',
        'nationality',
        'blood_group',
        'civil_status',
        'profession',
        'address_street',
        'address_number',
        'address_complement',
        'address_commune',
        'admission_date',
        'portable_number',
        'job_type',
        'job_replacement_id',
        'is_rescue_operator',
        'is_trauma_assistant',
        'is_shift_leader',
        'is_exchange',
        'is_penalty',
        'attendance_status',
        'is_titular',
        'replacement_until',
        'original_guardia_id',
        'original_attendance_status',
        'original_is_titular',
        'original_is_shift_leader',
        'original_is_exchange',
        'original_is_penalty',
        'original_job_replacement_id',
        'original_role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'date',
            'admission_date' => 'date',
            'is_driver' => 'boolean',
            'is_rescue_operator' => 'boolean',
            'is_trauma_assistant' => 'boolean',
            'is_shift_leader' => 'boolean',
            'is_exchange' => 'boolean',
            'is_penalty' => 'boolean',
            'is_titular' => 'boolean',
            'replacement_until' => 'datetime',
            'original_is_titular' => 'boolean',
            'original_is_shift_leader' => 'boolean',
            'original_is_exchange' => 'boolean',
            'original_is_penalty' => 'boolean',
        ];
    }

    /**
     * Accessor para obtener el tiempo de servicio formateado (Años y Meses).
     */
    public function getServiceTimeAttribute()
    {
        if (!$this->admission_date) {
            return 'Sin fecha';
        }

        $now = now();
        $diff = $this->admission_date->diff($now);

        $parts = [];

        if ($diff->y > 0) {
            $parts[] = $diff->y . ' ' . ($diff->y == 1 ? 'año' : 'años');
        }

        if ($diff->m > 0) {
            $parts[] = $diff->m . ' ' . ($diff->m == 1 ? 'mes' : 'meses');
        }

        // Si tiene años o meses, mostramos la combinación
        if (count($parts) > 0) {
            return implode(', ', $parts);
        }

        // Si no tiene ni un mes, mostramos días
        if ($diff->d > 0) {
            return $diff->d . ' ' . ($diff->d == 1 ? 'día' : 'días');
        }

        return 'Recién ingresado';
    }

    /**
     * Accessor para calcular years_of_service dinámicamente si se accede como propiedad.
     */
    public function getYearsOfServiceAttribute($value)
    {
        if ($this->admission_date) {
            return (int) $this->admission_date->diffInYears(now());
        }
        return $value ?? 0;
    }

    /**
     * Accessor para calcular la edad dinámicamente desde la fecha de nacimiento.
     */
    public function getAgeAttribute($value)
    {
        if ($this->birthdate) {
            return $this->birthdate->age;
        }
        return $value ?? 0;
    }

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function bedAssignments()
    {
        return $this->hasMany(BedAssignment::class);
    }

    public function staffEvents()
    {
        return $this->hasMany(StaffEvent::class);
    }

    public function replacements()
    {
        return $this->hasMany(StaffEvent::class, 'replacement_user_id');
    }

    public function cleaningAssignments()
    {
        return $this->hasMany(CleaningAssignment::class);
    }

    public function novelties()
    {
        return $this->hasMany(Novelty::class);
    }

    public function createdReminders()
    {
        return $this->hasMany(Reminder::class, 'created_by');
    }

    public function ledShifts()
    {
        return $this->hasMany(Shift::class, 'shift_leader_id');
    }

    public function shiftAssignments()
    {
        return $this->hasMany(ShiftUser::class);
    }

    public function jobReplacement()
    {
        return $this->belongsTo(User::class, 'job_replacement_id');
    }

    public function replacedBy()
    {
        return $this->hasOne(User::class, 'job_replacement_id');
    }
}
