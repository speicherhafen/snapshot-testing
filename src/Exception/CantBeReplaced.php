<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Exception;

use Exception;
use function sprintf;

final class CantBeReplaced extends Exception
{
    public function __construct(string $constraint, string $path)
    {
        $message = 'Replacement "%s" at path "%s" could not be performed.
                    Given value does not match the replacement\'s constraint.';

        parent::__construct(sprintf($message, $constraint, $path));
    }
}
