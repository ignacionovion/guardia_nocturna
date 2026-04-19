<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\OperationalHealthService;
use Illuminate\Console\Command;

/**
 * Debe ejecutarse en cada tick de schedule:run (cada minuto) para detectar cron caído.
 */
class OpsHeartbeatCommand extends Command
{
    protected $signature = 'ops:heartbeat';

    protected $description = 'Marca señal de vida del scheduler (operational_metrics)';

    public function handle(OperationalHealthService $health): int
    {
        $health->touchSchedulerHeartbeat();

        return self::SUCCESS;
    }
}
