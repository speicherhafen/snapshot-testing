<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Wildcard;

use function is_int;

final class IntegerOrNullWildcard implements Wildcard
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
        return $mixed === null || is_int($mixed);
    }
}
