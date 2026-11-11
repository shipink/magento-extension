<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model\Pure;

/**
 * Decides which Shipink order event, if any, a Magento order state change should emit.
 */
class OrderEventDecider
{
    /**
     * Returns 'order-canceled', 'order-updated' or null when nothing should be sent.
     *
     * @param string|null $oldState  Previous Magento state, null for a new order.
     * @param string|null $oldStatus Previous Magento status.
     * @param string      $newState  New Magento state.
     * @param string      $newStatus New Magento status.
     */
    public function decide(?string $oldState, ?string $oldStatus, string $newState, string $newStatus): ?string
    {
        if ($oldState === null) {
            return null; // new orders are sent by checkout_submit_all_after
        }
        if ($newState === 'canceled' && $oldState !== 'canceled') {
            return 'order-canceled';
        }
        if ($newState !== $oldState || $newStatus !== $oldStatus) {
            return $newState === 'canceled' ? null : 'order-updated';
        }
        return null;
    }
}
