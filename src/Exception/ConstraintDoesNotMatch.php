<?php

declare(strict_types=1);

namespace KigaRoo\Exception;

use Exception;

final class ConstraintDoesNotMatch extends Exception
{
    public function __construct(string $constraint, string $path)
    {
        parent::__construct(sprintf('constraint "%s" at path "%s" does not match.', $constraint, $path));
    }
}
