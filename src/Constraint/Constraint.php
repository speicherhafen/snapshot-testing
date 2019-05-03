<?php

declare(strict_types=1);

namespace KigaRoo\Constraint;

interface Constraint 
{
    public function toString(): string;
    public function getPath(): string;
    public function match($mixed): bool;
}