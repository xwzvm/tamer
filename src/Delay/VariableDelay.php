<?php declare(strict_types=1);

namespace Xwzvm\Attempt\Delay;

/**
 * Base class for non-constant delays.
 *
 * @author Sergei Malyshev <xwzvm@yandex.ru>
 */
abstract class VariableDelay implements Time\InMicroseconds
{
    /**
     * @var Time\InMicroseconds
     */
    protected Time\InMicroseconds $time;

    /**
     * @return void
     */
    abstract protected function vary(): void;

    /**
     * @param Time\InMicroseconds $time
     */
    public function __construct(Time\InMicroseconds $time)
    {
        $this->time = $time;
    }

    /**
     * @inheritDoc
     */
    final public function microseconds(): float
    {
        try {
            return $this->time->microseconds();
        } finally {
            $this->vary();
        }
    }
}
