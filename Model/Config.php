<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Config
{
    public const ACCOUNT_ID = 'shipink/general/account_id';
    public const WEBHOOK_URL = 'shipink/general/webhook_url';
    public const CONNECTED_AT = 'shipink/general/connected_at';

    public function __construct(
        private readonly ReinitableConfigInterface $scopeConfig,
        private readonly WriterInterface $writer,
        private readonly TypeListInterface $cacheTypeList,
        private readonly DateTime $dateTime
    ) {
    }

    public function getAccountId(): string
    {
        return (string)$this->scopeConfig->getValue(self::ACCOUNT_ID);
    }

    public function getWebhookUrl(): string
    {
        return (string)$this->scopeConfig->getValue(self::WEBHOOK_URL);
    }

    public function getConnectedAt(): string
    {
        return (string)$this->scopeConfig->getValue(self::CONNECTED_AT);
    }

    public function isConnected(): bool
    {
        return $this->getAccountId() !== '' && $this->getWebhookUrl() !== '';
    }

    public function clear(): void
    {
        $this->writer->delete(self::ACCOUNT_ID);
        $this->writer->delete(self::WEBHOOK_URL);
        $this->writer->delete(self::CONNECTED_AT);
        $this->cacheTypeList->cleanType('config');
        $this->scopeConfig->reinit();
    }

    public function save(string $accountId, string $webhookUrl): void
    {
        $this->writer->save(self::ACCOUNT_ID, $accountId);
        $this->writer->save(self::WEBHOOK_URL, $webhookUrl);
        $this->writer->save(self::CONNECTED_AT, $this->dateTime->gmtDate());
        $this->cacheTypeList->cleanType('config');
        $this->scopeConfig->reinit();
    }
}
