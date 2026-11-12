<?php
declare(strict_types=1);

namespace Shipink\Shipink\Plugin;

use Magento\Framework\Escaper;
use Magento\Sales\Block\Adminhtml\Order\View;
use Shipink\Shipink\Model\Config;
use Shipink\Shipink\Model\Pure\WebhookUrlBuilder;

class OrderViewButton
{
    /**
     * @param Config $config
     * @param WebhookUrlBuilder $urls
     * @param Escaper $escaper
     */
    public function __construct(
        private readonly Config $config,
        private readonly WebhookUrlBuilder $urls,
        private readonly Escaper $escaper
    ) {
    }

    /**
     * Adds an "Open in Shipink" button to the order view when the store is connected.
     *
     * @param View $subject
     * @return void
     */
    public function beforeSetLayout(View $subject): void
    {
        if (!$this->config->isConnected() || !$subject->getOrderId()) {
            return;
        }
        $url = $this->urls->openInShipink((int)$subject->getOrderId(), $this->config->getAppHost());
        $subject->addButton('shipink_open', [
            'label' => __('Open in Shipink'),
            'class' => 'shipink-open',
            'onclick' => sprintf("window.open('%s', '_blank', 'noopener')", $this->escaper->escapeJs($url)),
        ]);
    }
}
