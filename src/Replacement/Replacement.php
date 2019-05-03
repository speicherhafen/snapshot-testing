<?php

declare(strict_types=1);

namespace KigaRoo\Replacement;

interface Replacement 
{
    public function toString(): string;
    public function atPath(): string;
    public function match($mixed): bool;
}