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
        $part = explode('.', $state)[0] ?? '';
        $json = base64_decode(strtr($part, '-_', '+/'), true);
        if ($json === false) {
            return '';
        }
        $data = json_decode($json, true);
        return is_array($data) && is_string($data['label'] ?? null) ? $data['label'] : '';
    }
}
