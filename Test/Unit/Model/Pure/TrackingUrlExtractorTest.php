<?php
declare(strict_types=1);

namespace Shipink\Shipink\Test\Unit\Model\Pure;

use PHPUnit\Framework\TestCase;
use Shipink\Shipink\Model\Pure\TrackingUrlExtractor;

class TrackingUrlExtractorTest extends TestCase
{
    public function testPrefersUrlContainingTheNumber(): void
    {
        $comments = [
            'Kargo takip: https://track.example.com/AAA',
            'Kargo takip: https://track.example.com/TRK9',
        ];
        $this->assertSame('https://track.example.com/TRK9', (new TrackingUrlExtractor())->extract($comments, 'TRK9'));
    }

    public function testFallsBackToLatestTrackingComment(): void
    {
        $comments = ['hello', 'Kargo takip: https://t.example/x?id=1'];
        $this->assertSame('https://t.example/x?id=1', (new TrackingUrlExtractor())->extract($comments, 'ZZZ'));
    }

    public function testIgnoresNonHttpsAndUnrelated(): void
    {
        $this->assertNull((new TrackingUrlExtractor())->extract(['see http://x.example', 'no link'], 'TRK9'));
    }
}
