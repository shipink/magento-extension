<?php
declare(strict_types=1);

namespace Shipink\Shipink\Test\Unit\Model\Pure;

use PHPUnit\Framework\TestCase;
use Shipink\Shipink\Model\Pure\UrlValidator;

class UrlValidatorTest extends TestCase
{
    private const PROD = 'app.shipink.io';
    private const DEV = 'app.dev.shipink.io';

    /** @dataProvider callbackUrls */
    public function testIsAllowedCallback(string $url, string $host, bool $expected): void
    {
        $this->assertSame($expected, (new UrlValidator())->isAllowedCallback($url, $host));
    }

    public static function callbackUrls(): array
    {
        return [
            'ok' => ['https://app.shipink.io/api/callback/magento', self::PROD, true],
            'port 443' => ['https://app.shipink.io:443/api/callback/magento', self::PROD, true],
            'uppercase host' => ['https://APP.SHIPINK.IO/api/callback/magento', self::PROD, true],
            'http' => ['http://app.shipink.io/api/callback/magento', self::PROD, false],
            'uppercase scheme' => ['HTTPS://app.shipink.io/api/callback/magento', self::PROD, false],
            'suffix host' => ['https://app.shipink.io.evil.com/api/callback/magento', self::PROD, false],
            'host in query' => ['https://evil.com/api/callback/magento?h=app.shipink.io', self::PROD, false],
            'userinfo' => ['https://user@app.shipink.io/api/callback/magento', self::PROD, false],
            'backslash userinfo' => ['https://evil.com\\@app.shipink.io/api/callback/magento', self::PROD, false],
            'javascript' => ['javascript:alert(1)', self::PROD, false],
            'empty' => ['', self::PROD, false],
            'port 8443' => ['https://app.shipink.io:8443/api/callback/magento', self::PROD, false],
            'fragment' => ['https://app.shipink.io/api/callback/magento#frag', self::PROD, false],
            'trailing dot' => ['https://app.shipink.io./api/callback/magento', self::PROD, false],
            'query' => ['https://app.shipink.io/api/callback/magento?x=1', self::PROD, false],
            'return path' => ['https://app.shipink.io/callback/magento', self::PROD, false],
            'wrong path' => ['https://app.shipink.io/api/callback/magento/x', self::PROD, false],
            'no path' => ['https://app.shipink.io', self::PROD, false],
            'other host' => ['https://api.shipink.io/api/callback/magento', self::PROD, false],
            'dev host on prod' => ['https://app.dev.shipink.io/api/callback/magento', self::PROD, false],
            'dev host on dev' => ['https://app.dev.shipink.io/api/callback/magento', self::DEV, true],
            'prod host on dev' => ['https://app.shipink.io/api/callback/magento', self::DEV, false],
            'empty host' => ['https://app.shipink.io/api/callback/magento', '', false],
        ];
    }

    /** @dataProvider returnUrls */
    public function testIsAllowedReturn(string $url, string $host, bool $expected): void
    {
        $this->assertSame($expected, (new UrlValidator())->isAllowedReturn($url, $host));
    }

    public static function returnUrls(): array
    {
        return [
            'ok' => ['https://app.shipink.io/callback/magento', self::PROD, true],
            'query ok' => ['https://app.shipink.io/callback/magento?lang=tr', self::PROD, true],
            'uppercase host' => ['https://APP.SHIPINK.IO/callback/magento', self::PROD, true],
            'callback path' => ['https://app.shipink.io/api/callback/magento', self::PROD, false],
            'wrong path' => ['https://app.shipink.io/callback/woocommerce', self::PROD, false],
            'fragment' => ['https://app.shipink.io/callback/magento#x', self::PROD, false],
            'port 8443' => ['https://app.shipink.io:8443/callback/magento', self::PROD, false],
            'trailing dot' => ['https://app.shipink.io./callback/magento', self::PROD, false],
            'backslash userinfo' => ['https://evil.com\\@app.shipink.io/callback/magento', self::PROD, false],
            'uppercase scheme' => ['HTTPS://app.shipink.io/callback/magento', self::PROD, false],
            'other host' => ['https://evil.com/callback/magento', self::PROD, false],
            'dev host on prod' => ['https://app.dev.shipink.io/callback/magento', self::PROD, false],
            'dev host on dev' => ['https://app.dev.shipink.io/callback/magento', self::DEV, true],
        ];
    }

    /** @dataProvider webhookUrls */
    public function testIsAllowedWebhookBase(string $url, bool $expected): void
    {
        $this->assertSame($expected, (new UrlValidator())->isAllowedWebhookBase($url));
    }

    public static function webhookUrls(): array
    {
        return [
            'prod' => ['https://api.shipink.io/webhook', true],
            'dev' => ['https://api.dev.shipink.io/webhook', true],
            'uppercase host' => ['https://API.SHIPINK.IO/webhook', true],
            'http' => ['http://api.shipink.io/webhook', false],
            'bare domain' => ['https://shipink.io/webhook', false],
            'lookalike' => ['https://evilshipink.io/webhook', false],
            'suffix trick' => ['https://api.shipink.io.evil.com/webhook', false],
            'trailing dot' => ['https://api.shipink.io./webhook', false],
            'userinfo' => ['https://api.shipink.io@evil.com/webhook', false],
            'backslash userinfo' => ['https://evil.com\\@api.shipink.io/webhook', false],
            'port 8443' => ['https://api.shipink.io:8443/webhook', false],
            'empty' => ['', false],
        ];
    }
}
