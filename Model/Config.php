<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Shipink\Shipink\Model\Pure\UrlValidator;

class Config
{
    public const ACCOUNT_ID = 'shipink/general/account_id';
    public const WEBHOOK_URL = 'shipink/general/webhook_url';
    public const CONNECTED_AT = 'shipink/general/connected_at';
    /** Shipink app host; only set by config:set / env.php (default in etc/config.xml). */
    public const APP_HOST = 'shipink/general/app_host';
    public const DEFAULT_APP_HOST = 'app.shipink.io';

    public function __construct(
        private readonly ReinitableConfigInterface $scopeConfig,
        private readonly WriterInterface $writer,
        private readonly TypeListInterface $cacheTypeList,
        private readonly DateTime $dateTime,
        private readonly UrlValidator $urlValidator
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

    /**
     * Shipink app host the connect flow and links use, e.g. app.shipink.io.
     */
    public function getAppHost(): string
    {
        $host = strtolower(trim((string)$this->scopeConfig->getValue(self::APP_HOST)));
        return $host !== '' ? $host : self::DEFAULT_APP_HOST;
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

    /**
     * Saves the connection. Returns false (and saves nothing) when the webhook
     * URL is not an https URL on a *.shipink.io host.
     *
     * @param string $accountId  Shipink sales channel account id.
     * @param string $webhookUrl Shipink webhook base URL.
     */
    public function save(string $accountId, string $webhookUrl): bool
    {
        if (!$this->urlValidator->isAllowedWebhookBase($webhookUrl)) {
            return false;
        }
        $this->writer->save(self::ACCOUNT_ID, $accountId);
        $this->writer->save(self::WEBHOOK_URL, $webhookUrl);
        $this->writer->save(self::CONNECTED_AT, $this->dateTime->gmtDate());
        $this->cacheTypeList->cleanType('config');
        $this->scopeConfig->reinit();
        return true;
    }
}
