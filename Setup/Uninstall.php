<?php
declare(strict_types=1);

namespace Shipink\Shipink\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Shipink\Shipink\Model\IntegrationActivator;

/**
 * Removes the module's settings and revokes Shipink's API access: deletes the
 * Shipink integration and its OAuth consumer (its tokens go with it), the same
 * way Magento's System > Integrations > Delete does.
 */
class Uninstall implements UninstallInterface
{
    /**
     * @param IntegrationServiceInterface $integrationService
     * @param OauthServiceInterface $oauthService
     */
    public function __construct(
        private readonly IntegrationServiceInterface $integrationService,
        private readonly OauthServiceInterface $oauthService
    ) {
    }

    /**
     * @inheritdoc
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        $connection->delete($setup->getTable('core_config_data'), ['path LIKE ?' => 'shipink/%']);
        $connection->delete($setup->getTable('core_config_data'), ['path LIKE ?' => 'carriers/shipink/%']);

        $integration = $this->integrationService->findByName(IntegrationActivator::NAME);
        if ($integration->getId()) {
            $data = $this->integrationService->delete((int)$integration->getId());
            if (!empty($data['consumer_id'])) {
                $this->oauthService->deleteConsumer((int)$data['consumer_id']);
            }
        }
        $setup->endSetup();
    }
}
