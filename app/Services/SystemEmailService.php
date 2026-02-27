<?php

namespace App\Services;

use App\Mail\SystemNotificationMail;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SystemEmailService
{
    public static function shouldSend(string $type, ?string $actorEmail = null): bool
    {
        $enabled = SystemSetting::getValue('mail_enabled_' . $type, '0') === '1';
        if (!$enabled) {
            return false;
        }

        $allowed = trim((string) SystemSetting::getValue('mail_allowed_trigger_emails', ''));
        if ($allowed === '') {
            return true;
        }

        if (!$actorEmail) {
            return false;
        }

        $allowedList = collect(explode(',', $allowed))
            ->map(fn ($v) => strtolower(trim((string) $v)))
            ->filter()
            ->values();

        return $allowedList->contains(strtolower(trim((string) $actorEmail)));
    }

    public static function recipients(): array
    {
        $value = (string) SystemSetting::getValue('mail_recipients', '');
        if (trim($value) === '') {
            $value = (string) env('MAIL_RECIPIENTS', '');
        }
        return collect(explode(',', $value))
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values()
            ->toArray();
    }

    public static function from(): array
    {
        $address = (string) SystemSetting::getValue('mail_from_address', config('mail.from.address'));
        if (trim($address) === '') {
            $address = (string) env('MAIL_FROM_ADDRESS', config('mail.from.address'));
        }

        $name = (string) SystemSetting::getValue('mail_from_name', config('mail.from.name'));
        if (trim($name) === '') {
            $name = (string) env('MAIL_FROM_NAME', config('mail.from.name'));
        }

        return [
            'address' => $address,
            'name' => $name,
        ];
    }

    public static function send(
        string $type, 
        string $subject, 
        array $lines, 
        ?string $actorEmail = null,
        ?string $senderName = null,
        ?string $senderRole = null,
        ?string $sourceLabel = null,
        array $fileAttachments = []
    ): void
    {
        if (!static::shouldSend($type, $actorEmail)) {
            Log::info('System email skipped (disabled or unauthorized)', [
                'type' => $type,
                'subject' => $subject,
                'actor' => $actorEmail,
            ]);
            return;
        }

        $recipients = static::recipients();
        if (empty($recipients)) {
            Log::info('System email skipped (no recipients configured)', [
                'type' => $type,
                'subject' => $subject,
            ]);
            return;
        }

        $from = static::from();

        try {
            Log::info('System email sending', [
                'type' => $type,
                'subject' => $subject,
                'to' => $recipients,
                'from' => $from,
            ]);
            Mail::to($recipients)->send(new SystemNotificationMail(
                fromAddress: (string) ($from['address'] ?? ''),
                fromName: (string) ($from['name'] ?? ''),
                mailSubject: $subject,
                lines: $lines,
                fileAttachments: $fileAttachments,
                notificationType: $type,
                sourceLabel: $sourceLabel,
                senderName: $senderName ?? (auth()->user()?->name ?? 'Sistema'),
                senderEmail: $actorEmail ?? auth()->user()?->email,
                senderRole: $senderRole ?? auth()->user()?->role,
            ));
        } catch (\Throwable $e) {
            Log::error('System email send failed', [
                'type' => $type,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
