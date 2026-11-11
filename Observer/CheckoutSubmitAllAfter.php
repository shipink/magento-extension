<?php
declare(strict_types=1);

namespace Shipink\Shipink\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Shipink\Shipink\Model\WebhookSender;

class CheckoutSubmitAllAfter implements ObserverInterface
{
    public function __construct(private readonly WebhookSender $sender)
    {
    }

    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();
        $orders = $event->getOrders() ?: array_filter([$event->getOrder()]);
        foreach ($orders as $order) {
            $this->sender->send('order-placed', (int)$order->getId(), (string)$order->getIncrementId());
        }
    }
}
