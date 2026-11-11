<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model\Pure;

/**
 * Checks the URLs Shipink hands the module during connect. Both connect URLs
 * must be on the configured Shipink app host (shipink/general/app_host).
 */
class UrlValidator
{
    public const CALLBACK_PATH = '/api/callback/magento';
    public const RETURN_PATH = '/callback/magento';
    public const WEBHOOK_HOST_SUFFIX = '.shipink.io';

    /**
     * True for https://<host>/api/callback/magento with no query.
     *
     * @param string $url  URL to check.
     * @param string $host Expected Shipink app host, e.g. app.shipink.io.
     */
    public function isAllowedCallback(string $url, string $host): bool
    {
        return $this->check($url, $host, self::CALLBACK_PATH, false);
    }

    /**
     * True for https://<host>/callback/magento, query allowed.
     *
     * @param string $url  URL to check.
     * @param string $host Expected Shipink app host, e.g. app.shipink.io.
     */
    public function isAllowedReturn(string $url, string $host): bool
    {
        return $this->check($url, $host, self::RETURN_PATH, true);
    }

    /**
     * True for an https URL whose host ends in .shipink.io (Shipink's webhook base).
     *
     * @param string $url URL to check.
     */
    public function isAllowedWebhookBase(string $url): bool
    {
        $parts = $this->baseParts($url);
        if ($parts === null) {
            return false;
        }
        return str_ends_with(strtolower((string)($parts['host'] ?? '')), self::WEBHOOK_HOST_SUFFIX);
    }

    /**
     * Shared checks for the connect URLs.
     *
     * @param string $url        URL to check.
     * @param string $host       Expected host.
     * @param string $path       Exact path required.
     * @param bool   $allowQuery Whether a query string is allowed.
     */
    private function check(string $url, string $host, string $path, bool $allowQuery): bool
    {
        $parts = $this->baseParts($url);
        if ($parts === null || $host === '') {
            return false;
        }
        if (strtolower((string)($parts['host'] ?? '')) !== strtolower($host)) {
            return false;
        }
        if (($parts['path'] ?? '') !== $path) {
            return false;
        }
        return $allowQuery || !isset($parts['query']);
    }

    /**
     * Parses an https URL without userinfo or fragment, port absent or 443.
     *
     * @param string $url URL to parse.
     * @return array<string, mixed>|null Parts, or null when any rule fails.
     */
    private function baseParts(string $url): ?array
    {
        $parts = parse_url($url);
        if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }
        if (isset($parts['fragment']) || (isset($parts['port']) && (int)$parts['port'] !== 443)) {
            return null;
        }
        return $parts;
    }
}
