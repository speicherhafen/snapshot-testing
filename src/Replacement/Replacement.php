<?php

declare(strict_types=1);

namespace KigaRoo\Replacement;

interface Replacement
{
    /**
     * return the replacement value. type depends on the replacement.
     *
     * @return mixed
     */
    public function getValue();
    public function atPath(): string;
    public function match($mixed): bool;
}