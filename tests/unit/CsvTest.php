<?php

declare(strict_types=1);

namespace Tests;

use KigaRoo\SnapshotTesting\MatchesSnapshots;
use KigaRoo\SnapshotTesting\Wildcard\DateTimeWildcard;
use KigaRoo\SnapshotTesting\Wildcard\StringWildcard;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    use MatchesSnapshots;

    public function testCsv() : void
    {
        $data = <<<CSV
D;Wahr;50;"Homer, Simpson";"id card 123";20991231;General
M;Wahr;50;"Homer, Simpson";"id card 123";20991231;General
A;Wahr;50;"Vorname, Nachname";"id card 123";20991231;General
CSV;

        $wildcards = [
            new StringWildcard('[*][2]'),
            new DateTimeWildcard('[*][5]', 'Ymd'),
        ];

        $this->assertMatchesCsvSnapshot($data, $wildcards);
    }
    
    public function testCsvWithNonDefaultConfig() : void
    {
        $data = <<<CSV
D,Wahr,50,'Homer, Simpson','id card 123',20991231,General
M,Wahr,50,'Homer, Simpson','id card 123',20991231,General
A,Wahr,50,'Vorname, Nachname','id card 123',20991231,General
CSV;

        $wildcards = [
            new StringWildcard('[*][2]'),
            new DateTimeWildcard('[*][5]', 'Ymd'),
        ];

        $this->assertMatchesCsvSnapshot($data, $wildcards, ',', "'");
    }
}
