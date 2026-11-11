<?php
declare(strict_types=1);

namespace Shipink\Shipink\Test\Unit\Model\Pure;

use PHPUnit\Framework\TestCase;
use Shipink\Shipink\Model\Pure\NonceCheck;

class NonceCheckTest extends TestCase
{
    /** @dataProvider cases */
    public function testIsValid(string $given, string $stored, int $issuedAt, int $now, bool $expected): void
    {
        $this->assertSame($expected, (new NonceCheck())->isValid($given, $stored, $issuedAt, $now));
    }

    public static function cases(): array
    {
        return [
            'match, fresh' => ['abc', 'abc', 1000, 1000, true],
            'match, 29 min' => ['abc', 'abc', 1000, 1000 + 1799, true],
            'match, 30 min' => ['abc', 'abc', 1000, 1000 + 1800, false],
            'mismatch' => ['abd', 'abc', 1000, 1000, false],
            'nothing stored' => ['abc', '', 1000, 1000, false],
            'nothing given' => ['', 'abc', 1000, 1000, false],
            'both empty' => ['', '', 1000, 1000, false],
            'issued in future' => ['abc', 'abc', 2000, 1000, false],
        ];
    }
}
