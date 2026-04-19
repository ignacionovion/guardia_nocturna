<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\OperationalAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Email de alertas operativas: nueva, escalación warning→critical, resolución.
 *
 * Eventos futuros (Telegram, Slack): mismo Mailable o Notifications API.
 */
final class OperationalAlertMail extends Mailable
{
    use Queueable;

    public readonly string $panelUrl;

    public function __construct(
        public OperationalAlert $alert,
        public string $eventType,
    ) {
        $configured = config('operational_alerts.panel_url');
        $this->panelUrl = (is_string($configured) && $configured !== '')
            ? $configured
            : rtrim((string) config('app.url'), '/') . '/admin';
    }

    public function envelope(): Envelope
    {
        $env = config('app.env');
        $subject = match ($this->eventType) {
            'escalated' => "[GuardiAPP][{$env}] Escalación: {$this->alert->title}",
            'resolved' => "[GuardiAPP][{$env}] Resuelto: {$this->alert->title}",
            default => "[GuardiAPP][{$env}] Alerta: {$this->alert->title}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.operational-alert',
            with: [
                'alert' => $this->alert,
                'eventType' => $this->eventType,
                'panelUrl' => $this->panelUrl,
                'appEnv' => config('app.env'),
                'triggeredAt' => now()->timezone(config('app.timezone'))->format('d/m/Y H:i:s'),
            ],
        );
    }
}
