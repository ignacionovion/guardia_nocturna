<?php

declare(strict_types=1);

namespace App\Support;

final class CentralUrls
{
    public static function billingPlans(): string
    {
        return self::centralUrl('/admin/billing/plans');
    }

    public static function billingIndex(): string
    {
        return self::centralUrl('/admin/billing');
    }

    private static function centralUrl(string $path): string
    {
        $domain = self::primaryCentralDomain();
        $scheme = self::scheme();

        return $scheme.'://'.$domain.$path;
    }

    private static function primaryCentralDomain(): string
    {
        $domains = array_values(array_filter(
            config('tenancy.central_domains', []),
            static fn (string $d): bool => ! in_array($d, ['127.0.0.1', 'localhost'], true)
        ));

        return $domains[0] ?? 'localhost';
    }

    private static function scheme(): string
    {
        if (app()->environment('local')) {
            return 'http';
        }

        return 'https';
    }
}
