<?php

declare(strict_types=1);

namespace KigaRoo\Constraint;

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
    
    
}