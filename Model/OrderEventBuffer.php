<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model;

class OrderEventBuffer
{
    /** @var array<int, array{state: ?string, status: ?string}> */
    private array $before = [];

    /**
     * Keeps an order's state and status as they were before the save.
     *
     * @param int $orderId
     * @param string|null $state
     * @param string|null $status
     * @return void
     */
    public function remember(int $orderId, ?string $state, ?string $status): void
    {
        $this->before[$orderId] = ['state' => $state, 'status' => $status];
    }

    /**
     * Returns and forgets the stored state and status for an order, or null when none is stored.
     *
     * @param int $orderId
     * @return array{state: ?string, status: ?string}|null
     */
    public function take(int $orderId): ?array
    {
        $value = $this->before[$orderId] ?? null;
        unset($this->before[$orderId]);
        return $value;
    }
}
