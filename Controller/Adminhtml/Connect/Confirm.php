<?php
declare(strict_types=1);

namespace Shipink\Shipink\Controller\Adminhtml\Connect;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Shipink\Shipink\Model\Config;
use Shipink\Shipink\Model\IntegrationActivator;
use Shipink\Shipink\Model\Pure\UrlValidator;
use Shipink\Shipink\Model\ShipinkHttp;

class Confirm extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Shipink_Shipink::connect';

    public function __construct(
        Action\Context $context,
        private readonly UrlValidator $urlValidator,
        private readonly IntegrationActivator $activator,
        private readonly ShipinkHttp $http,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductMetadataInterface $productMetadata,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $req = $this->getRequest();
        $state = (string)$req->getParam('state');
        $callback = (string)$req->getParam('callback_url');
        $return = (string)$req->getParam('return_url');
        $redirect = $this->resultRedirectFactory->create();

        if ($state === '' || !$this->urlValidator->isAllowedShipinkUrl($callback)
            || !$this->urlValidator->isAllowedShipinkUrl($return)) {
            $this->messageManager->addErrorMessage(__('Invalid Shipink connect request.'));
            return $redirect->setPath('shipink/dashboard/index');
        }

        try {
            $credentials = $this->activator->activate();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $redirect->setPath('shipink/dashboard/index');
        }

        $storeUrl = rtrim((string)$this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB, true), '/');
        $reply = $this->http->postJson($callback, $credentials + [
            'state' => $state,
            'store_url' => $storeUrl,
            'magento_version' => $this->productMetadata->getVersion(),
        ], 20);

        $body = $reply['body'] ?? [];
        if ($reply['status'] !== 200 || empty($body['webhook_url']) || empty($body['sales_channel_account_id'])) {
            // Status only: the request carried credentials and the reply body is untrusted.
            $this->logger->error('Shipink connect failed', ['status' => $reply['status']]);
            // activate() already rotated the token Shipink held, so the old connection is dead.
            $this->config->clear();
            return $redirect->setUrl($return . (str_contains($return, '?') ? '&' : '?') . 'success=0');
        }

        $this->config->save((string)$body['sales_channel_account_id'], (string)$body['webhook_url']);
        return $redirect->setUrl($return . (str_contains($return, '?') ? '&' : '?') . 'success=1');
    }
}
