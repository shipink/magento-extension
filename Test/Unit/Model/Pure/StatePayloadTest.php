<?php
declare(strict_types=1);

namespace Shipink\Shipink\Test\Unit\Model\Pure;

use PHPUnit\Framework\TestCase;
use Shipink\Shipink\Model\Pure\StatePayload;

class StatePayloadTest extends TestCase
{
    public function testLabel(): void
    {
        $json = json_encode(['userId' => 'u', 'label' => 'Acme Ltd', 'exp' => 1]);
        $state = rtrim(strtr(base64_encode($json), '+/', '-_'), '=') . '.sig';
        $this->assertSame('Acme Ltd', (new StatePayload())->label($state));
    }

    public function testGarbage(): void
    {
        $this->assertSame('', (new StatePayload())->label('%%%'));
    }
}
