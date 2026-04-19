<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\OperationalAlertService;
use Illuminate\Console\Command;

final class OpsCheckAlertsCommand extends Command
{
    protected $signature = 'ops:check-alerts {--no-notify : Sincroniza alertas sin enviar emails}';

    protected $description = 'Sincroniza alertas operativas con la salud actual y envía notificaciones si corresponde';

    public function handle(OperationalAlertService $alerts): int
    {
        $send = ! $this->option('no-notify');
        $result = $alerts->syncFromHealthSummary($send);

        $this->info('Alertas abiertas: '.$result['open_count']);
        $this->info('Destinatarios notificados (suma por envío): '.$result['emails_sent']);
        if (! $send) {
            $this->warn('Modo --no-notify: no se enviaron correos.');
        }

        return self::SUCCESS;
    }
}
