<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Exception;

use Exception;
use function sprintf;

final class InvalidMappingPath extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('Path to "%s" could not be mapped.', $path));
    }
}
