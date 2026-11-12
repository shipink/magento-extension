<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model;

use Magento\Backend\Model\Session;
use Shipink\Shipink\Model\Pure\NonceCheck;

/**
 * Ties a connect request to a Connect click in this admin session: the
 * dashboard issues a nonce, Shipink passes it back, Confirm uses it up.
 */
class ConnectNonce
{
    private const KEY = 'shipink_connect_nonce';

    /**
     * @param Session $session
     * @param NonceCheck $check
     */
    public function __construct(
        private readonly Session $session,
        private readonly NonceCheck $check
    ) {
    }

    /**
     * Stores a new nonce in the admin session and returns it.
     */
    public function issue(): string
    {
        $nonce = bin2hex(random_bytes(16));
        $this->session->setData(self::KEY, ['value' => $nonce, 'at' => time()]);
        return $nonce;
    }

    /**
     * True when $given matches the session nonce and it has not expired.
     *
     * @param string $given Nonce from the request.
     */
    public function isValid(string $given): bool
    {
        return $this->matches($given, $this->session->getData(self::KEY));
    }

    /**
     * Removes the session nonce and returns whether $given matched it.
     *
     * @param string $given Nonce from the request.
     */
    public function consume(string $given): bool
    {
        return $this->matches($given, $this->session->getData(self::KEY, true));
    }

    /**
     * Compares against a stored session entry.
     *
     * @param string $given  Nonce from the request.
     * @param mixed  $stored Session entry.
     */
    private function matches(string $given, mixed $stored): bool
    {
        if (!is_array($stored)) {
            return false;
        }
        return $this->check->isValid(
            $given,
            is_string($stored['value'] ?? null) ? $stored['value'] : '',
            is_int($stored['at'] ?? null) ? $stored['at'] : 0,
            time()
        );
    }
}
