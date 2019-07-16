<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Exception;

use Exception;
use function sprintf;

final class WildcardMismatch extends Exception
{
    public function __construct(string $wildcard, string $path)
    {
        $message = 'Wildcard "%s" at path "%s" could not be performed.
                    Given value does not match the wildcards constraint.';

        parent::__construct(sprintf($message, $wildcard, $path));
    }
}
