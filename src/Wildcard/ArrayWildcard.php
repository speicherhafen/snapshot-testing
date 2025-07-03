<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Wildcard;

use Webmozart\Assert\Assert;

final class ArrayWildcard implements Wildcard
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
        return is_array($mixed);
    }
}
