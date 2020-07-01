<?php declare(strict_types=1);

namespace Tamer\Test\Time;

use PHPUnit\Framework\TestCase;
use Tamer\Time\Minute;

/**
 * @author Sergei Malyshev <xwzvm@yandex.ru>
 */
final class MinuteTest extends TestCase
{
    private const MICROSECONDS_PER_MINUTE = 60_000_000;

    /**
     * @param float $amount
     * @param float $expected
     * @dataProvider data
     */
    public function testMicroseconds(float $amount, float $expected): void
    {
        $minutes = new Minute($amount);

        $this->assertEquals($expected, $minutes->microseconds());
    }

    /**
     * @return array[]
     */
    public function data(): array
    {
        $data = [];

        for ($i = 0; $i < 3; ++$i) {
            $amount = (float) mt_rand(0, 100);
            $data[] = [$amount, $amount * self::MICROSECONDS_PER_MINUTE];
        }

        return $data;
    }
}