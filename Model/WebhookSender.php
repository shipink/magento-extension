<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model;

use Psr\Log\LoggerInterface;
use Shipink\Shipink\Model\Pure\WebhookUrlBuilder;

class WebhookSender
{
    /** Seconds sends stay skipped after Shipink was unreachable. */
    public const BREAKER_SECONDS = 60;

    /** @var int|null Unix time Shipink was last found unreachable. */
    private ?int $trippedAt = null;

    public function __construct(
        private readonly Config $config,
        private readonly ShipinkHttp $http,
        private readonly WebhookUrlBuilder $urls,
        private readonly LoggerInterface $logger
    ) {
    }

    public function send(string $event, int $entityId, string $incrementId): void
    {
        if (!$this->config->isConnected()) {
            return;
        }
        if ($this->trippedAt !== null && time() - $this->trippedAt < self::BREAKER_SECONDS) {
            $this->logger->debug(
                'Shipink webhook skipped (Shipink unreachable)',
                ['event' => $event, 'order' => $entityId]
            );
            return;
        }
        $this->trippedAt = null;
        try {
            $url = $this->urls->webhook($this->config->getWebhookUrl(), $event, $this->config->getAccountId());
            $reply = $this->http->postJson($url, ['entity_id' => $entityId, 'increment_id' => $incrementId], 3);
            if ($reply['status'] === 0) {
                $this->trippedAt = time();
                $this->logger->warning(
                    'Shipink unreachable; skipping Shipink webhooks for ' . self::BREAKER_SECONDS . ' seconds'
                );
                return;
            }
            if ($reply['status'] < 200 || $reply['status'] >= 300) {
                $this->logger->warning(
                    'Shipink webhook not accepted',
                    ['event' => $event, 'order' => $entityId, 'status' => $reply['status']]
                );
            }
        } catch (\Throwable $e) {
            $this->logger->error(
                'Shipink webhook failed',
                ['event' => $event, 'order' => $entityId, 'error' => $e->getMessage()]
            );
        }
    }
}
