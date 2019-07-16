<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Wildcard;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class UuidWildcard implements Wildcard
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
        try {
            Assert::uuid($mixed);

            return true;
        } catch (InvalidArgumentException $exception) {
            return false;
        }
    }
}
