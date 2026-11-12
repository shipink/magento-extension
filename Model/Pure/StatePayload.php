<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model\Pure;

/**
 * Reads display fields from the state nuxt-auth signed. Never trusted for
 * authorization: nuxt-auth verifies the signature when the module posts it back.
 */
class StatePayload
{
    /**
     * Returns the 'label' field of the state's JSON part, or '' when unreadable.
     *
     * @param string $state Signed state string, base64url(json).signature.
     */
    public function label(string $state): string
    {
        $data = $this->decode($state);
        return is_string($data['label'] ?? null) ? $data['label'] : '';
    }

    /**
     * Returns the 'exp' field (unix seconds), or null when missing.
     *
     * Unsigned: only used to skip obviously stale requests before touching the integration.
     *
     * @param string $state Signed state string, base64url(json).signature.
     */
    public function exp(string $state): ?int
    {
        $exp = $this->decode($state)['exp'] ?? null;
        return is_int($exp) ? $exp : null;
    }

    /**
     * Decodes the state's JSON part.
     *
     * @param string $state Signed state string.
     * @return array<string, mixed>
     */
    private function decode(string $state): array
    {
        $part = explode('.', $state)[0];
        // phpcs:ignore Magento2.Functions.DiscouragedFunction -- pure class, no Magento dependency
        $json = base64_decode(strtr($part, '-_', '+/'), true);
        if ($json === false) {
            return [];
        }
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }
}
