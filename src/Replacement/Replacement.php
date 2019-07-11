<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Replacement;

interface Replacement
{
    /**
     * Generic value for all replacement types
     */
    public const VALUE = 'REPLACEMENT';

    /**
     * path to where array or property value will be replaced
     */
    public function atPath() : string;

    /**
     * gets called to ensure a formal correct value before it gets replaced
     *
     * @param mixed $mixed
     */
    public function match($mixed) : bool;
}
