<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Wildcard;

final class DateTimeOrNullWildcard extends BaseDateTimeWildcard
{
    /**
     * @param mixed $mixed
     */
    public function match($mixed) : bool
    {
        if ($mixed === null) {
            return true;
        }

        return parent::match($mixed);
    }
}
