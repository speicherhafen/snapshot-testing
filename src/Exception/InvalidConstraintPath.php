<?php

declare(strict_types=1);

namespace KigaRoo\Exception;

use Exception;

final class InvalidConstraintPath extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('path to "%s" could not be found.', $path));
    }
}
