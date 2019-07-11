<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Replacement;

use function is_int;

final class IntegerReplacement implements Replacement
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
        return is_int($mixed);
    }
}
