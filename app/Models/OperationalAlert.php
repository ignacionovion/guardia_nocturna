<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Alertas operativas persistidas (scheduler, backups, billing, tenant:run).
 *
 * Una fila por alert_key: se reutiliza al reabrir tras resolución.
 */
class OperationalAlert extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_RESOLVED = 'resolved';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_CRITICAL = 'critical';

    protected $connection = 'central';

    protected $table = 'operational_alerts';

    protected $fillable = [
        'alert_key',
        'source',
        'severity',
        'status',
        'title',
        'message',
        'first_triggered_at',
        'last_triggered_at',
        'last_notified_at',
        'resolved_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'first_triggered_at' => 'datetime',
            'last_triggered_at' => 'datetime',
            'last_notified_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
