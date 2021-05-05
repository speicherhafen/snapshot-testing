<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Wildcard;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class ObjectOrNullWildcard implements Wildcard
{
    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function atPath() : string
    {
        return $this->path;
    }

    /**
     * @param mixed $mixed
     */
    public function match($mixed) : bool
    {
        if (null === $mixed) {
            return true;
        }

        try {
            Assert::object($mixed);

            return true;
        } catch (InvalidArgumentException $exception) {
            return false;
        }
    }
}
