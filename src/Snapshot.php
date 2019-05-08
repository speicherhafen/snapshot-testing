<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting;

use KigaRoo\SnapshotTesting\Replacement\Replacement;

final class Snapshot
{
    /** @var string */
    private $id;

    /** @var string */
    private $content;

    /** @var \KigaRoo\Driver */
    private $driver;

    private function __construct(
        string $id,
        string $content,
        Driver $driver
    ) {
        $this->id      = $id;
        $this->driver  = $driver;
        $this->content = $content;
    }

    public static function forTestCase(
        string $id,
        string $content,
        Driver $driver
    ) : self {
        return new self($id, $content, $driver);
    }

    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @param Replacement[] $replacements
     */
    public function assertMatches(string $actual, array $replacements = []) : void
    {
        $this->driver->match($this->content, $actual, $replacements);
    }

    public function getDriver() : Driver
    {
        return $this->driver;
    }

    public function getContent() : string
    {
        return $this->content;
    }

    /**
     * @param Replacement[] $replacements
     */
    public function update(string $actual, array $replacements = []) : void
    {
        $this->content = $this->driver->serialize($actual, $replacements);
    }
}
