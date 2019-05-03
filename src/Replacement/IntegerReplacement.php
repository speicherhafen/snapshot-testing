<?php

declare(strict_types=1);

namespace KigaRoo\Replacement;

final class IntegerReplacement implements Replacement
{
    /**
     * @var string 
     */
    private $path;
    
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function atPath(): string
    {
        return $this->path;
    }

    public function getValue()
    {
        return 666;
    }

    public function match($mixed): bool
    {
        return is_int($mixed);
    }
}