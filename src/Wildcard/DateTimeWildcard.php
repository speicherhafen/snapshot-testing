<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Wildcard;

use DateTime;
use Throwable;

final class DateTimeWildcard implements Wildcard
{
    /** @var string */
    private $path;

    /** @var string */
    private $format;

    public function __construct(string $path, string $format = DateTime::ATOM)
    {
        $this->path   = $path;
        $this->format = $format;
    }

    public function atPath() : string
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return (new DateTime('2019-01-01'))->format($this->format);
    }

    /**
     * @param mixed $mixed
     */
    public function match($mixed) : bool
    {
        try {
            $dateTime = DateTime::createFromFormat($this->format, $mixed);

            return $dateTime !== false;
        } catch (Throwable $exception) {
            return false;
        }
    }
}