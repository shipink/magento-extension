<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model\Pure;

class UrlValidator
{
    public const ALLOWED_HOSTS = ['app.shipink.io', 'app.dev.shipink.io'];

    public function isAllowedShipinkUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }
        if (isset($parts['fragment']) || (isset($parts['port']) && (int)$parts['port'] !== 443)) {
            return false;
        }
        return in_array(strtolower($parts['host'] ?? ''), self::ALLOWED_HOSTS, true);
    }
}
