<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Replacement;

interface Replacement
{
    /**
     * return the replacement value. type depends on the replacement implementation.
     *
     * @return mixed
     */
    public function getValue();

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
