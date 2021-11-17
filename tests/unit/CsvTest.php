<?php

declare(strict_types=1);

namespace Tests;

use KigaRoo\SnapshotTesting\MatchesSnapshots;
use KigaRoo\SnapshotTesting\Wildcard\BooleanWildcard;
use KigaRoo\SnapshotTesting\Wildcard\DateTimeOrNullWildcard;
use KigaRoo\SnapshotTesting\Wildcard\DateTimeWildcard;
use KigaRoo\SnapshotTesting\Wildcard\IntegerWildcard;
use KigaRoo\SnapshotTesting\Wildcard\StringWildcard;
use KigaRoo\SnapshotTesting\Wildcard\UuidWildcard;
use PHPUnit\Framework\TestCase;

final class CsvTest extends TestCase
{
    use MatchesSnapshots;

    public function testCsv() : void
    {
        $data = <<<CSV
"b84c9b7f-1ebb-49b6-9d18-4305932b2dd1";D;Wahr;50;"Homer, Simpson";"id card 123";20991231;General
"b84c9b7f-1ebb-49b6-9d18-4305932b2dd1";M;Wahr;50;"Homer, Simpson";"id card 123";20991231;General
"b84c9b7f-1ebb-49b6-9d18-4305932b2dd1";A;Wahr;50;"Vorname, Nachname";"id card 123";20991231;General
CSV;

        $wildcards = [
            new StringWildcard('[*][3]'),
            new DateTimeWildcard('[*][6]', 'Ymd'),
            new UuidWildcard('[*][0]'),
        ];

        $this->assertMatchesCsvSnapshot($data, $wildcards);
    }
    
    public function testCsvWithNonDefaultConfig() : void
    {
        $data = <<<CSV
'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1',D,Wahr,50,'Homer, Simpson','id card 123',20991231,General
'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1',M,Wahr,50,'Homer, Simpson','id card 123',20991231,General
'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1',A,Wahr,50,'Vorname, Nachname','id card 123',20991231,General
CSV;

        $wildcards = [
            new StringWildcard('[*][3]'),
            new DateTimeWildcard('[*][6]', 'Ymd'),
            new UuidWildcard('[*][0]'),
        ];

        $this->assertMatchesCsvSnapshot($data, $wildcards, ',', "'");
    }
}
