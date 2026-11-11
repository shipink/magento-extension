<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model;

use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

class ShipinkHttp
{
    public function __construct(
        private readonly CurlFactory $curlFactory,
        private readonly Json $json
    ) {
    }

    /**
     * @return array{status: int, body: array|null}
     */
    public function postJson(string $url, array $body, int $timeout): array
    {
        $curl = $this->curlFactory->create();
        $curl->setTimeout($timeout);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 1);
        $curl->addHeader('Content-Type', 'application/json');
        $curl->addHeader('Accept', 'application/json');
        try {
            $curl->post($url, $this->json->serialize($body));
        } catch (\Exception $e) {
            return ['status' => 0, 'body' => null];
        }
        $decoded = null;
        try {
            $decoded = $this->json->unserialize((string)$curl->getBody());
        } catch (\InvalidArgumentException $e) {
            $decoded = null;
        }
        return ['status' => (int)$curl->getStatus(), 'body' => is_array($decoded) ? $decoded : null];
    }
}
