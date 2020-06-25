<?php declare(strict_types=1);

namespace Xwzvm\Attempt\Test;

use PHPUnit\Framework\TestCase;
use Xwzvm\Attempt\Attempt;
use Xwzvm\Attempt\Interrupt\Interrupt;
use Xwzvm\Attempt\Problem;

/**
 * @author Sergei Malyshev <xwzvm@yandex.ru>
 */
final class AttemptTest extends TestCase
{
    /**
     * @param callable $action
     * @param int $bound
     * @param mixed[] $arguments
     * @param int $expected
     * @dataProvider data
     */
    public function testInvoke(callable $action, int $bound, array $arguments, int $expected): void
    {
        $interrupt = $this->createMock(Interrupt::class);
        $interrupt->expects($this->exactly($bound))->method('halt');

        $sieve = $this->createMock(Problem\Resolver::class);
        $sieve->expects($this->exactly($bound))->method('pass');

        $attempt = new Attempt($interrupt, $sieve);

        $result = $attempt($action, $bound + 1)(...$arguments);

        $this->assertEquals($expected, $result);
    }

    /**
     * @param callable $action
     * @param int $bound
     * @param mixed[] $arguments
     * @dataProvider data
     */
    public function testNotEnoughAttempts(callable $action, int $bound, array $arguments): void
    {
        $this->expectException(\Throwable::class);

        $times = mt_rand(1, $bound - 1);

        $interrupt = $this->createMock(Interrupt::class);
        $interrupt->expects($this->exactly($times))->method('halt');

        $sieve = $this->createMock(Problem\Resolver::class);
        $sieve->expects($this->exactly($times))->method('pass');

        $attempt = new Attempt($interrupt, $sieve);

        $attempt($action, $times)(...$arguments);
    }

    /**
     * @return void
     */
    public function testInvalidTimesNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument $times must be at least 1.');

        $interrupt = $this->createMock(Interrupt::class);
        $interrupt->expects($this->never())->method('halt');

        $sieve = $this->createMock(Problem\Resolver::class);
        $sieve->expects($this->never())->method('pass');

        $attempt = new Attempt($interrupt, $sieve);

        $attempt(function (): void {}, 0);
    }

    /**
     * @param callable $action
     * @param int $times
     * @param string $expected
     * @dataProvider problems
     */
    public function testLastProblemWasThrown(callable $action, int $times, string $expected): void
    {
        /** @var class-string<\Throwable> $expected */
        $this->expectException($expected);

        $interrupt = $this->createMock(Interrupt::class);
        $interrupt->expects($this->exactly($times))->method('halt');

        $sieve = $this->createMock(Problem\Resolver::class);
        $sieve->expects($this->exactly($times))->method('pass');

        $attempt = new Attempt($interrupt, $sieve);

        $attempt($action, $times)();
    }

    /**
     * @return array[]
     */
    public function data(): array
    {
        $data = [];

        for ($i = 0; $i < 3; ++$i) {
            $bound = mt_rand(2, 10);
            $argument = mt_rand(0, 100);

            $square = function (int $x) use ($bound): int {
                static $count = 0;

                if ($count++ < $bound) {
                    throw new \RuntimeException();
                }

                return $x * $x;
            };

            $data[] = [$square, $bound, [$argument], $argument * $argument];
        }

        return $data;
    }

    /**
     * @return array[]
     */
    public function problems(): array
    {
        $data = [];

        for ($i = 0; $i < 3; ++$i) {
            $times = mt_rand(1, 10);

            $square = function (): void {
                static $count = 0;

                if ($count++ % 2 === 0) {
                    throw new \RuntimeException();
                }

                throw new \LogicException();
            };

            $data[] = [$square, $times, $times % 2 === 0 ? \LogicException::class : \RuntimeException::class];
        }

        return $data;
    }
}
