<?php
declare(strict_types=1);

namespace Shipink\Shipink\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        $connection->delete($setup->getTable('core_config_data'), ['path LIKE ?' => 'shipink/%']);
        $connection->delete($setup->getTable('core_config_data'), ['path LIKE ?' => 'carriers/shipink/%']);
        $setup->endSetup();
    }
}
