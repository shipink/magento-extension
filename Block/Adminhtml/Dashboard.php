<?php
declare(strict_types=1);

namespace Shipink\Shipink\Block\Adminhtml;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Block\Template;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Shipink\Shipink\Model\Config;

class Dashboard extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly FrontNameResolver $frontNameResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isConnected(): bool
    {
        return $this->config->isConnected();
    }

    public function getConnectedAt(): string
    {
        return $this->config->getConnectedAt();
    }

    public function getConnectUrl(): string
    {
        $base = rtrim((string)$this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB, true), '/');
        return 'https://app.shipink.io/?' . http_build_query([
            'source' => 'magento',
            'url' => $base,
            'admin_path' => (string)$this->frontNameResolver->getFrontName(),
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function getDashboardUrl(): string
    {
        return 'https://app.shipink.io/orders?sales_channel_id=magento';
    }
}
