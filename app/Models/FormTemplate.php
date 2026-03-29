<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FormTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'estructura_json',
        'activo',
        'created_by',
    ];

    protected $casts = [
        'estructura_json' => 'array',
        'activo' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->nombre);
            }
        });

        static::updating(function ($template) {
            if ($template->isDirty('nombre') && empty($template->slug)) {
                $template->slug = Str::slug($template->nombre);
            }
        });
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getEstructuraAttribute()
    {
        return $this->estructura_json ?? [];
    }

    public function setEstructuraAttribute($value)
    {
        $this->estructura_json = $value;
    }
}
