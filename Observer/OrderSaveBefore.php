<?php
declare(strict_types=1);

namespace Shipink\Shipink\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Shipink\Shipink\Model\OrderEventBuffer;

class OrderSaveBefore implements ObserverInterface
{
    public function __construct(private readonly OrderEventBuffer $buffer)
    {
    }

    public function execute(Observer $observer): void
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($order && $order->getId()) {
            $this->buffer->remember((int)$order->getId(), $order->getOrigData('state'), $order->getOrigData('status'));
        }
    }
}
