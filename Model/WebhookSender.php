<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model;

use Psr\Log\LoggerInterface;
use Shipink\Shipink\Model\Pure\WebhookUrlBuilder;

class WebhookSender
{
    private bool $shipinkDown = false;

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
        if ($this->shipinkDown) {
            $this->logger->debug(
                'Shipink webhook skipped (Shipink unreachable)',
                ['event' => $event, 'order' => $entityId]
            );
            return;
        }
        try {
            $url = $this->urls->webhook($this->config->getWebhookUrl(), $event, $this->config->getAccountId());
            $reply = $this->http->postJson($url, ['entity_id' => $entityId, 'increment_id' => $incrementId], 3);
            if ($reply['status'] === 0) {
                $this->shipinkDown = true;
                $this->logger->warning('Shipink unreachable; skipping further Shipink webhooks for this request');
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
