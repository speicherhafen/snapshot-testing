<?php

declare(strict_types=1);

namespace KigaRoo\Constraint;

use Webmozart\Assert\Assert;

final class UuidConstraint implements Constraint
{
    /**
     * @var string 
     */
    private $path;
    
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function toString(): string
    {
        return 'uuid';
    }

    public function match($mixed): bool
    {
        try {
            Assert::uuid($mixed);
            return true;
        }
        catch(\InvalidArgumentException $exception)
        {
            return false;
        }
    }
}