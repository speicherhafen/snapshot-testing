<?php

declare(strict_types=1);

namespace KigaRoo\Exception;

use Exception;

final class CannotBeReplaced extends Exception
{
    public function __construct(string $constraint, string $path)
    {
        parent::__construct(sprintf('replacement "%s" at path "%s" could not be performed. given value does not match the replacement\'s constraint.', $constraint, $path));
    }
}
