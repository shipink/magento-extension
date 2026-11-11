<?php
declare(strict_types=1);

namespace Shipink\Shipink\Test\Unit\Model\Pure;

use PHPUnit\Framework\TestCase;
use Shipink\Shipink\Model\Pure\OrderEventDecider;

class OrderEventDeciderTest extends TestCase
{
    public function testDecide(): void
    {
        $d = new OrderEventDecider();
        $this->assertSame('order-canceled', $d->decide('processing', 'processing', 'canceled', 'canceled'));
        $this->assertNull($d->decide('canceled', 'canceled', 'canceled', 'canceled'));
        $this->assertSame('order-updated', $d->decide('new', 'pending', 'processing', 'processing'));
        $this->assertSame('order-updated', $d->decide('processing', 'processing', 'processing', 'fraud'));
        $this->assertNull($d->decide('processing', 'processing', 'processing', 'processing'));
        $this->assertNull($d->decide(null, null, 'new', 'pending'));
    }
}
