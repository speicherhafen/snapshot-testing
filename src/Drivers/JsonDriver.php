<?php

declare(strict_types=1);

namespace KigaRoo\Drivers;

use PHPUnit\Framework\Assert;
use KigaRoo\Driver;
use KigaRoo\Exceptions\CantBeSerialized;

final class JsonDriver implements Driver
{
    public function serialize(string $data): string
    {
        $data = json_decode($data, true);
        
        if(false === $data)
        {
            throw new CantBeSerialized('not a valid json string.');
        }

        return json_encode($data, JSON_PRETTY_PRINT).PHP_EOL;
    }

    public function extension(): string
    {
        return 'json';
    }

    public function match(string $expected, string $actual)
    {
        Assert::assertJsonStringEqualsJsonString($expected, $actual);
    }
}
