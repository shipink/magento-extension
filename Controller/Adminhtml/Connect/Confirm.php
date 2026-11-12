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
use Shipink\Shipink\Model\ConnectNonce;
use Shipink\Shipink\Model\IntegrationActivator;
use Shipink\Shipink\Model\Pure\StatePayload;
use Shipink\Shipink\Model\Pure\UrlValidator;
use Shipink\Shipink\Model\ShipinkHttp;

class Confirm extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Shipink_Shipink::connect';

    /** Error codes sent back to Shipink as return_url?success=0&error=<code>. */
    public const ERROR_EXPIRED = 'expired';
    public const ERROR_ACTIVATION_FAILED = 'activation_failed';
    public const ERROR_CALLBACK_UNREACHABLE = 'callback_unreachable';
    public const ERROR_CALLBACK_REJECTED = 'callback_rejected';
    public const ERROR_INVALID_WEBHOOK_URL = 'invalid_webhook_url';

    /**
     * @param Action\Context $context
     * @param UrlValidator $urlValidator
     * @param IntegrationActivator $activator
     * @param ShipinkHttp $http
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetadata
     * @param LoggerInterface $logger
     * @param ConnectNonce $connectNonce
     * @param StatePayload $statePayload
     */
    public function __construct(
        Action\Context $context,
        private readonly UrlValidator $urlValidator,
        private readonly IntegrationActivator $activator,
        private readonly ShipinkHttp $http,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductMetadataInterface $productMetadata,
        private readonly LoggerInterface $logger,
        private readonly ConnectNonce $connectNonce,
        private readonly StatePayload $statePayload
    ) {
        parent::__construct($context);
    }

    /**
     * Completes the connect: activates the integration, hands the credentials to Shipink and redirects.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $req = $this->getRequest();
        $redirect = $this->resultRedirectFactory->create();

        // Single use: the session nonce is removed whether or not it matched.
        if (!$this->connectNonce->consume((string)$req->getParam('nonce'))) {
            $this->messageManager->addErrorMessage(
                __('This Shipink connect link has expired or was not started from this admin. Click Connect again.')
            );
            return $redirect->setPath('shipink/dashboard/index');
        }

        $state = (string)$req->getParam('state');
        $callback = (string)$req->getParam('callback_url');
        $return = (string)$req->getParam('return_url');
        $host = $this->config->getAppHost();
        if ($state === '' || !$this->urlValidator->isAllowedCallback($callback, $host)
            || !$this->urlValidator->isAllowedReturn($return, $host)) {
            $this->messageManager->addErrorMessage(__('Invalid Shipink connect request.'));
            return $redirect->setPath('shipink/dashboard/index');
        }

        // Stale request: leave the current connection (and its token) alone.
        $exp = $this->statePayload->exp($state);
        if ($exp === null || $exp <= time()) {
            return $redirect->setUrl($this->returnUrl($return, self::ERROR_EXPIRED));
        }

        try {
            $credentials = $this->activator->activate();
        } catch (LocalizedException $e) {
            $this->logger->error('Shipink connect: activation failed', ['error' => $e->getMessage()]);
            // The token Shipink held may already have been rotated.
            $this->config->clear();
            return $redirect->setUrl($this->returnUrl($return, self::ERROR_ACTIVATION_FAILED));
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
            $code = $reply['status'] === 0 ? self::ERROR_CALLBACK_UNREACHABLE : self::ERROR_CALLBACK_REJECTED;
            return $redirect->setUrl($this->returnUrl($return, $code));
        }

        if (!$this->config->save((string)$body['sales_channel_account_id'], (string)$body['webhook_url'])) {
            $this->logger->error('Shipink connect failed: webhook_url is not a Shipink URL');
            $this->config->clear();
            return $redirect->setUrl($this->returnUrl($return, self::ERROR_INVALID_WEBHOOK_URL));
        }
        return $redirect->setUrl($this->returnUrl($return, null));
    }

    /**
     * Appends success=1, or success=0&error=<code>, to the Shipink return URL.
     *
     * @param string      $return Validated return URL.
     * @param string|null $error  Error code, null on success.
     */
    private function returnUrl(string $return, ?string $error): string
    {
        $query = $error === null ? ['success' => '1'] : ['success' => '0', 'error' => $error];
        return $return . (str_contains($return, '?') ? '&' : '?') . http_build_query($query);
    }
}
