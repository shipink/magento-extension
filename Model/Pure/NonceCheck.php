<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model\Pure;

/**
 * Compares the nonce Shipink passed back with the one the admin's Connect
 * click stored in the admin session.
 */
class NonceCheck
{
    /** Seconds a Connect click stays valid. */
    public const TTL = 1800;

    /**
     * True when $given equals $stored and the stored nonce is younger than TTL.
     *
     * @param string $given    Nonce from the request.
     * @param string $stored   Nonce from the admin session ('' when none).
     * @param int    $issuedAt Unix time the stored nonce was issued.
     * @param int    $now      Current unix time.
     */
    public function isValid(string $given, string $stored, int $issuedAt, int $now): bool
    {
        if ($given === '' || $stored === '' || !hash_equals($stored, $given)) {
            return false;
        }
        $age = $now - $issuedAt;
        return $age >= 0 && $age < self::TTL;
    }
}
