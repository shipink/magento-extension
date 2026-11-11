<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model\Pure;

/**
 * Builds the Shipink webhook endpoint URLs and the "Open in Shipink" link.
 */
class WebhookUrlBuilder
{
    /**
     * Webhook endpoint for an event, tagged with the account and the Magento sales channel.
     *
     * @param string $base      Webhook base URL.
     * @param string $event     Event name, e.g. order-placed.
     * @param string $accountId Shipink sales channel account id.
     */
    public function webhook(string $base, string $event, string $accountId): string
    {
        return rtrim($base, '/') . '/' . $event . '?' . http_build_query([
            'sales_channel_account_id' => $accountId,
            'sales_channel_id' => 'magento',
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Link that opens the given Magento order in the Shipink app.
     *
     * @param int    $orderId Magento order id.
     * @param string $appHost Shipink app host, e.g. app.shipink.io.
     */
    public function openInShipink(int $orderId, string $appHost): string
    {
        return 'https://' . $appHost . '/orders?' . http_build_query([
            'sales_channel_id' => 'magento',
            'sales_channel_order_id' => $orderId,
            'action' => 'open',
        ], '', '&', PHP_QUERY_RFC3986);
    }
}
