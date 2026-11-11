<?php
declare(strict_types=1);

namespace Shipink\Shipink\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Shipink\Shipink\Model\OrderEventBuffer;
use Shipink\Shipink\Model\Pure\OrderEventDecider;
use Shipink\Shipink\Model\WebhookSender;

class OrderSaveCommitAfter implements ObserverInterface
{
    public function __construct(
        private readonly OrderEventBuffer $buffer,
        private readonly OrderEventDecider $decider,
        private readonly WebhookSender $sender
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if (!$order || !$order->getId()) {
            return;
        }
        $before = $this->buffer->take((int)$order->getId());
        if ($before === null) {
            return;
        }
        $event = $this->decider->decide(
            $before['state'],
            $before['status'],
            (string)$order->getState(),
            (string)$order->getStatus()
        );
        if ($event !== null) {
            $this->sender->send($event, (int)$order->getId(), (string)$order->getIncrementId());
        }
    }
}
