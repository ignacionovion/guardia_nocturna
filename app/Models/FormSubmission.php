<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'user_id',
        'data_json',
        'estado',
    ];

    protected $casts = [
        'data_json' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getDataAttribute()
    {
        return $this->data_json ?? [];
    }

    public function setDataAttribute($value)
    {
        $this->data_json = $value;
    }

    public function scopeEnviados($query)
    {
        return $query->where('estado', 'enviado');
    }

    public function scopeBorradores($query)
    {
        return $query->where('estado', 'borrador');
    }

    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
