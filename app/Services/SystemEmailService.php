<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\SystemNotificationMail;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SystemEmailService
{
    /**
     * Tipo canónico para logs / política (tras aplicar aliases).
     */
    public static function normalizeMailType(string $type): string
    {
        $key = strtolower(trim($type));
        $aliases = config('mail_strategy.aliases', []);

        return is_string($aliases[$key] ?? null) ? (string) $aliases[$key] : $key;
    }

    /**
     * Política global (config/mail_strategy.php).
     */
    public static function policyAllows(string $canonicalType): bool
    {
        $types = config('mail_strategy.types', []);

        if (! array_key_exists($canonicalType, $types)) {
            return (bool) config('mail_strategy.allow_unknown_types', true);
        }

        return (bool) ($types[$canonicalType]['enabled'] ?? false);
    }

    /**
     * Sufijo para SystemSetting mail_enabled_{suffix} o null si no aplica.
     */
    public static function legacySettingSuffix(string $canonicalType): ?string
    {
        $types = config('mail_strategy.types', []);
        $suffix = $types[$canonicalType]['legacy_setting_suffix'] ?? null;

        if ($suffix === null || $suffix === '') {
            return null;
        }

        return (string) $suffix;
    }

    /**
     * Si la política global bloquea el envío, registra trazabilidad y devuelve false.
     */
    public static function ensurePolicyAllows(string $rawType, string $subject, ?string $actorEmail = null): bool
    {
        $canonical = self::normalizeMailType($rawType);
        if (self::policyAllows($canonical)) {
            return true;
        }

        Log::warning('Email blocked by mail_strategy policy', [
            'type_raw' => $rawType,
            'type' => $canonical,
            'subject' => $subject,
            'actor' => $actorEmail,
            'reason' => 'disabled_by_policy',
        ]);

        return false;
    }

    public static function shouldSend(string $type, ?string $actorEmail = null): bool
    {
        $canonical = self::normalizeMailType($type);

        if (! self::policyAllows($canonical)) {
            return false;
        }

        $suffix = self::legacySettingSuffix($canonical);
        if ($suffix !== null) {
            $enabled = SystemSetting::getValue('mail_enabled_' . $suffix, '0') === '1';
            if (! $enabled) {
                return false;
            }
        }

        $allowed = trim((string) SystemSetting::getValue('mail_allowed_trigger_emails', ''));
        if ($allowed === '') {
            return true;
        }

        if (! $actorEmail) {
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
    ): void {
        $canonical = self::normalizeMailType($type);

        if (! self::ensurePolicyAllows($type, $subject, $actorEmail)) {
            return;
        }

        if (! static::shouldSend($type, $actorEmail)) {
            $suffix = self::legacySettingSuffix($canonical);
            $legacyOff = $suffix !== null && SystemSetting::getValue('mail_enabled_' . $suffix, '0') !== '1';
            Log::info('System email skipped (system settings or trigger allowlist)', [
                'type_raw' => $type,
                'type' => $canonical,
                'subject' => $subject,
                'actor' => $actorEmail,
                'reason' => $legacyOff ? 'disabled_in_system_settings' : 'trigger_email_not_allowed',
            ]);

            return;
        }

        $recipients = static::recipients();
        if ($recipients === []) {
            Log::info('System email skipped (no recipients configured)', [
                'type_raw' => $type,
                'type' => $canonical,
                'subject' => $subject,
            ]);

            return;
        }

        $from = static::from();

        try {
            Log::info('System email sending', [
                'type_raw' => $type,
                'type' => $canonical,
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
                notificationType: $canonical,
                sourceLabel: $sourceLabel,
                senderName: $senderName ?? (auth()->user()?->name ?? 'Sistema'),
                senderEmail: $actorEmail ?? auth()->user()?->email,
                senderRole: $senderRole ?? auth()->user()?->role,
            ));
        } catch (\Throwable $e) {
            Log::error('System email send failed', [
                'type_raw' => $type,
                'type' => $canonical,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
