<?php
declare(strict_types=1);

namespace Shipink\Shipink\Test\Unit\Model\Pure;

use PHPUnit\Framework\TestCase;
use Shipink\Shipink\Model\Pure\StatePayload;

class StatePayloadTest extends TestCase
{
    private static function state(array $payload): string
    {
        return rtrim(strtr(base64_encode((string)json_encode($payload)), '+/', '-_'), '=') . '.sig';
    }

    public function testLabel(): void
    {
        $state = self::state(['userId' => 'u', 'label' => 'Acme Ltd', 'exp' => 1]);
        $this->assertSame('Acme Ltd', (new StatePayload())->label($state));
    }

    public function testGarbage(): void
    {
        $this->assertSame('', (new StatePayload())->label('%%%'));
        $this->assertNull((new StatePayload())->exp('%%%'));
    }

    public function testExp(): void
    {
        $this->assertSame(1700000000, (new StatePayload())->exp(self::state(['exp' => 1700000000])));
    }

    public function testExpMissingOrNotInt(): void
    {
        $this->assertNull((new StatePayload())->exp(self::state(['label' => 'x'])));
        $this->assertNull((new StatePayload())->exp(self::state(['exp' => '1700000000'])));
        $this->assertNull((new StatePayload())->exp(''));
    }
}
