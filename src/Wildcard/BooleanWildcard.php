<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Wildcard;

use function is_bool;

final class BooleanWildcard implements Wildcard
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
        return is_bool($mixed);
    }
}
