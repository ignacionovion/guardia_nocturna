<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Métricas operativas persistidas en BD central (heartbeat scheduler, backups, billing, tenant:run).
 */
class OperationalMetric extends Model
{
    protected $connection = 'central';

    protected $table = 'operational_metrics';

    protected $fillable = [
        'metric_key',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
