<?php

namespace App\Console\Commands;

use App\Services\SystemEmailService;
use Illuminate\Console\Command;

class TestEmailCommand extends Command
{
    protected $signature = 'mail:test {--email= : Email de prueba (por defecto: ignacio.n12@gmail.com)}';
    protected $description = 'Envía un correo de prueba para verificar la configuración SMTP';

    public function handle(): int
    {
        $email = $this->option('email') ?? 'ignacio.n12@gmail.com';

        $this->info('📧 Enviando correo de prueba...');
        $this->info("   Destinatario: {$email}");
        $this->info('   Servidor: ' . config('mail.mailers.smtp.host'));
        $this->info('   Puerto: ' . config('mail.mailers.smtp.port'));
        $this->info('   From: ' . config('mail.from.address'));
        $this->newLine();

        try {
            // Forzar el envío directo (bypass de validaciones)
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\SystemNotificationMail(
                fromAddress: config('mail.from.address'),
                fromName: config('mail.from.name'),
                mailSubject: '✅ Prueba de correo - GuardiAPP',
                lines: [
                    'Este es un correo de prueba para verificar la configuración SMTP.',
                    'Si estás leyendo esto, ¡la configuración está correcta!',
                    '<strong>Datos de configuración:</strong>',
                    '• Servidor: ' . config('mail.mailers.smtp.host'),
                    '• Puerto: ' . config('mail.mailers.smtp.port'),
                    '• Usuario: ' . config('mail.mailers.smtp.username'),
                    '• Fecha: ' . now()->format('d-m-Y H:i:s'),
                ]
            ));

            $this->info('✅ Correo enviado exitosamente!');
            $this->info("   Revisa la bandeja de entrada de: {$email}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Error al enviar correo:');
            $this->error('   ' . $e->getMessage());
            $this->newLine();
            $this->warn('Posibles causas:');
            $this->warn('   • Contraseña incorrecta en MAIL_PASSWORD');
            $this->warn('   • Servidor SMTP no responde');
            $this->warn('   • Firewall bloqueando conexión');
            $this->warn('   • TLS/SSL no configurado correctamente');

            return self::FAILURE;
        }
    }
}
