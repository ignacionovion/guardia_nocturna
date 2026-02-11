<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardiaArchive extends Model
{
    protected $fillable = ['guardia_id', 'archived_at', 'label'];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function items()
    {
        return $this->hasMany(GuardiaArchiveItem::class);
    }
}
