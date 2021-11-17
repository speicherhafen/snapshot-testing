<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Driver;

use KigaRoo\SnapshotTesting\Accessor;
use KigaRoo\SnapshotTesting\Driver;
use KigaRoo\SnapshotTesting\Exception\CantBeSerialized;
use KigaRoo\SnapshotTesting\Wildcard\Wildcard;
use PHPUnit\Framework\Assert;
use stdClass;
use const PHP_EOL;
use function is_string;
use function json_encode;

final class CsvDriver implements Driver
{
    /**
     * @var string 
     */
    private $fieldSeparator;
    
    /**
     * @var string 
     */
    private $fieldEnclosure;

    public function __construct(string $fieldSeparator, string $fieldEnclosure)
    {
        $this->fieldSeparator = $fieldSeparator;
        $this->fieldEnclosure = $fieldEnclosure;
    }

    public function serialize(string $csv): string
    {
        $data = $this->decode($csv);
        $handle = fopen('php://temp', 'r+');
        assert(is_resource($handle));
        foreach ($data as $line) {
            fputcsv($handle, $line, $this->fieldSeparator, $this->fieldEnclosure);
        }
        rewind($handle);
        $csvString = stream_get_contents($handle);
        assert(is_string($csvString));

        return $csvString.PHP_EOL;
    }

    public function extension(): string
    {
        return 'json';
    }

    /**
     * @param Wildcard[] $wildcards
     */
    public function match(string $expected, string $actual, array $wildcards = []): void
    {
        $actualArray = $this->decode($actual);
        $this->assertFields($actualArray, $wildcards);

        $actualArray = $this->replaceFields($actualArray, $wildcards);
        $actual = json_encode($actualArray);

        $expectedArray = $this->decode($expected);
        $expectedArray = $this->replaceFields($expectedArray, $wildcards);
        $expected = json_encode($expectedArray);

        Assert::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @param string|stdClass|Wildcard[] $data
     * @param Wildcard[]                 $wildcards
     */
    private function assertFields($data, array $wildcards): void
    {
        if (is_string($data)) {
            return;
        }

        foreach ($wildcards as $wildcard) {
            (new Accessor())->assertFields($data, $wildcard);
        }
    }

    /**
     * @param string[][] $data
     * @param Wildcard[] $wildcards
     */
    private function replaceFields(array $data, array $wildcards): array
    {
        foreach ($wildcards as $wildcard) {
            (new Accessor())->replaceFields($data, $wildcard);
        }

        return $data;
    }

    /**
     * @throws CantBeSerialized
     */
    private function decode(string $data): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $data);
        if ($lines === false) {
            throw new CantBeSerialized('Given string does not contain valid csv.');
        }
        $lines = array_filter(
            $lines, static function (string $line): bool {
                return '' !== trim($line);
            }
        );
        
        return array_map(
            function (string $line): array {
                return str_getcsv($line, $this->fieldSeparator, $this->fieldEnclosure);
            },
            $lines
        );
    }
}
