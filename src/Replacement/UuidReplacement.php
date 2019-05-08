<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Replacement;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class UuidReplacement implements Replacement
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
     * @return mixed
     */
    public function getValue()
    {
        return 'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1';
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
