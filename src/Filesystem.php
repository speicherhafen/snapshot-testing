<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting;

use const DIRECTORY_SEPARATOR;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function mkdir;

final class Filesystem
{
    /** @var string */
    private $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function path(string $filename) : string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . $filename;
    }

    public function has(string $filename) : bool
    {
        return file_exists($this->path($filename));
    }

    public function read(string $filename) : string
    {
        return file_get_contents($this->path($filename));
    }

    public function put(string $filename, string $contents) : void
    {
        if (! file_exists($this->basePath)) {
            mkdir($this->basePath, 0777, true);
        }

        file_put_contents($this->path($filename), $contents);
    }
}
