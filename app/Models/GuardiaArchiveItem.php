<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardiaArchiveItem extends Model
{
    protected $fillable = [
        'guardia_archive_id',
        'firefighter_id',
        'entity_type',
        'entity_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function archive()
    {
        return $this->belongsTo(GuardiaArchive::class, 'guardia_archive_id');
    }

    public function firefighter()
    {
        return $this->belongsTo(Bombero::class, 'firefighter_id');
    }
}
