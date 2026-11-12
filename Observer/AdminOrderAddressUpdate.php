<?php
declare(strict_types=1);

namespace Shipink\Shipink\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Shipink\Shipink\Model\WebhookSender;

/**
 * An admin edited an order's billing or shipping address (core AddressSave
 * controller, event admin_sales_order_address_update with 'order_id'). The
 * order itself is not saved, so tell Shipink here.
 */
class AdminOrderAddressUpdate implements ObserverInterface
{
    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param WebhookSender $sender
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly WebhookSender $sender,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer): void
    {
        try {
            $orderId = (int)$observer->getEvent()->getData('order_id');
            if ($orderId <= 0) {
                return;
            }
            $order = $this->orderRepository->get($orderId);
            $this->sender->send('order-updated', $orderId, (string)$order->getIncrementId());
        } catch (\Throwable $e) {
            $this->logger->error('Shipink address update webhook failed', ['error' => $e->getMessage()]);
        }
    }
}
