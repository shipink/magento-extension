<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model;

class OrderEventBuffer
{
    /** @var array<int, array{state: ?string, status: ?string}> */
    private array $before = [];

    public function remember(int $orderId, ?string $state, ?string $status): void
    {
        $this->before[$orderId] = ['state' => $state, 'status' => $status];
    }

    /** @return array{state: ?string, status: ?string}|null */
    public function take(int $orderId): ?array
    {
        $value = $this->before[$orderId] ?? null;
        unset($this->before[$orderId]);
        return $value;
    }
}
