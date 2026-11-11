<?php
declare(strict_types=1);

namespace Shipink\Shipink\Test\Unit\Model\Pure;

use PHPUnit\Framework\TestCase;
use Shipink\Shipink\Model\Pure\UrlValidator;

class UrlValidatorTest extends TestCase
{
    /** @dataProvider urls */
    public function testIsAllowedShipinkUrl(string $url, bool $expected): void
    {
        $this->assertSame($expected, (new UrlValidator())->isAllowedShipinkUrl($url));
    }

    public static function urls(): array
    {
        return [
            ['https://app.shipink.io/api/callback/magento', true],
            ['https://app.dev.shipink.io/callback/magento?success=1', true],
            ['http://app.shipink.io/api/callback/magento', false],
            ['https://app.shipink.io.evil.com/x', false],
            ['https://evil.com/?h=app.shipink.io', false],
            ['https://user@app.shipink.io/x', false],
            ['javascript:alert(1)', false],
            ['', false],
            ['https://app.shipink.io:8443/x', false],
            ['https://app.shipink.io:443/x', true],
            ['https://app.shipink.io/x#frag', false],
            ['https://APP.SHIPINK.IO/x', true],
            ['https://app.shipink.io./x', false],
            ['HTTPS://app.shipink.io/x', false],
            ['https://evil.com\@app.shipink.io/x', false],
        ];
    }
}
