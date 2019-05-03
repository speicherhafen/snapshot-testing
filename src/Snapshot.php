<?php

declare(strict_types=1);

namespace KigaRoo;

final class Snapshot
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $content;

    /**
     * @var \KigaRoo\Driver
     */
    private $driver;

    private function __construct(
        string $id,
        string $content,
        Driver $driver
    ) {
        $this->id = $id;
        $this->driver = $driver;
        $this->content = $content;
    }

    public static function forTestCase(
        string $id,
        string $content,
        Driver $driver
    ): self {
        return new self($id, $content, $driver);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function assertMatches(string $actual, ?array $fieldConstraints = null)
    {
        $this->driver->match($this->content, $actual, $fieldConstraints);
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }
}
