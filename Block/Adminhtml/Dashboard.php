<?php
declare(strict_types=1);

namespace Shipink\Shipink\Block\Adminhtml;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Block\Template;
use Magento\Framework\UrlInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\Integration;
use Magento\Store\Model\StoreManagerInterface;
use Shipink\Shipink\Model\Config;
use Shipink\Shipink\Model\ConnectNonce;
use Shipink\Shipink\Model\IntegrationActivator;

class Dashboard extends Template
{
    /** @var string|null Nonce issued for this render. */
    private ?string $nonce = null;

    public function __construct(
        Template\Context $context,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly FrontNameResolver $frontNameResolver,
        private readonly IntegrationServiceInterface $integrationService,
        private readonly ConnectNonce $connectNonce,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Connected only when the module has its Shipink settings and the Shipink
     * integration (API access) is still active.
     */
    public function isConnected(): bool
    {
        if (!$this->config->isConnected()) {
            return false;
        }
        $integration = $this->integrationService->findByName(IntegrationActivator::NAME);
        return $integration->getId() && (int)$integration->getStatus() === Integration::STATUS_ACTIVE;
    }

    public function getConnectedAt(): string
    {
        return $this->config->getConnectedAt();
    }

    public function canConnect(): bool
    {
        return (bool)$this->_authorization->isAllowed('Shipink_Shipink::connect');
    }

    /**
     * Shipink app link that starts the connect flow; carries a fresh session nonce.
     */
    public function getConnectUrl(): string
    {
        if ($this->nonce === null) {
            $this->nonce = $this->connectNonce->issue();
        }
        $base = rtrim((string)$this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB, true), '/');
        return 'https://' . $this->config->getAppHost() . '/?' . http_build_query([
            'source' => 'magento',
            'url' => $base,
            'admin_path' => (string)$this->frontNameResolver->getFrontName(),
            'nonce' => $this->nonce,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function getDashboardUrl(): string
    {
        return 'https://' . $this->config->getAppHost() . '/orders?sales_channel_id=magento';
    }
}
