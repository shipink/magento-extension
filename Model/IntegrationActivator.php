<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\ResourceModel\Integration as IntegrationResource;

/**
 * Does what Magento's own "Activate" button does for the Shipink integration,
 * then returns the OAuth values Shipink signs its requests with. Reconnecting
 * replaces the access token, so an old copy at Shipink stops working.
 */
class IntegrationActivator
{
    /** Integration name as declared in etc/integration.xml. */
    public const NAME = 'Shipink';

    /**
     * @param IntegrationServiceInterface $integrationService
     * @param OauthServiceInterface $oauthService
     * @param IntegrationResource $integrationResource
     */
    public function __construct(
        private readonly IntegrationServiceInterface $integrationService,
        private readonly OauthServiceInterface $oauthService,
        private readonly IntegrationResource $integrationResource
    ) {
    }

    /**
     * Activates the integration and returns fresh OAuth credentials.
     *
     * @return array{consumer_key: string, consumer_secret: string, access_token: string, access_token_secret: string}
     * @throws LocalizedException
     */
    public function activate(): array
    {
        $integration = $this->integrationService->findByName(self::NAME);
        if (!$integration->getId()) {
            throw new LocalizedException(__('The Shipink integration is missing. Run bin/magento setup:upgrade.'));
        }

        if (!$integration->getConsumerId()) {
            $consumer = $this->oauthService->createConsumer(['name' => 'Integration' . $integration->getId()]);
            $integration->setConsumerId($consumer->getId());
        }
        $consumerId = (int)$integration->getConsumerId();

        $this->oauthService->createAccessToken($consumerId, true);
        $integration->setStatus(Integration::STATUS_ACTIVE);
        $this->integrationResource->save($integration);

        $consumer = $this->oauthService->loadConsumer($consumerId);
        $token = $this->oauthService->getAccessToken($consumerId);
        if (!$token) {
            throw new LocalizedException(__('Could not create the Shipink access token.'));
        }

        return [
            'consumer_key' => (string)$consumer->getKey(),
            'consumer_secret' => (string)$consumer->getSecret(),
            'access_token' => (string)$token->getToken(),
            'access_token_secret' => (string)$token->getSecret(),
        ];
    }
}
