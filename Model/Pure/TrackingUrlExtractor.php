<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model\Pure;

/**
 * Finds the carrier tracking URL in order comments written as "Kargo takip: <url>".
 */
class TrackingUrlExtractor
{
    private const PATTERN = '~Kargo takip:\s*(https://\S+)~u';

    /**
     * Prefers the newest URL containing the track number, otherwise the newest URL.
     *
     * @param string[] $comments    Comment texts, newest last.
     * @param string   $trackNumber Carrier track number to prefer in the URL.
     */
    public function extract(array $comments, string $trackNumber): ?string
    {
        $found = [];
        foreach ($comments as $comment) {
            if (preg_match(self::PATTERN, (string)$comment, $m)) {
                $found[] = $m[1];
            }
        }
        foreach (array_reverse($found) as $url) {
            if ($trackNumber !== '' && strpos($url, $trackNumber) !== false) {
                return $url;
            }
        }
        return $found ? end($found) : null;
    }
}
