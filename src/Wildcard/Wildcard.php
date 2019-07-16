<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Wildcard;

interface Wildcard
{
    /**
     * Generic replacement for all wildcards
     */
    public const REPLACEMENT = '*';

    /**
     * path to where array or property value will be "wildcarded"
     */
    public function atPath() : string;

    /**
     * gets called to ensure a formal correct value
     *
     * @param mixed $mixed
     */
    public function match($mixed) : bool;
}
