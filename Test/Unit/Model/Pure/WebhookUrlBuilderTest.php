<?php
declare(strict_types=1);

namespace Shipink\Shipink\Test\Unit\Model\Pure;

use PHPUnit\Framework\TestCase;
use Shipink\Shipink\Model\Pure\WebhookUrlBuilder;

class WebhookUrlBuilderTest extends TestCase
{
    public function testWebhook(): void
    {
        $this->assertSame(
            'https://api.shipink.io/webhook/order-placed?sales_channel_account_id=acc%201&sales_channel_id=magento',
            (new WebhookUrlBuilder())->webhook('https://api.shipink.io/webhook/', 'order-placed', 'acc 1')
        );
    }

    public function testOpenInShipink(): void
    {
        $this->assertSame(
            'https://app.shipink.io/orders?sales_channel_id=magento&sales_channel_order_id=42&action=open',
            (new WebhookUrlBuilder())->openInShipink(42)
        );
    }
}
