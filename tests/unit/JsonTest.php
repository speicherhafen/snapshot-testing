<?php

declare(strict_types=1);

namespace Tests;

use KigaRoo\SnapshotTesting\MatchesSnapshots;
use KigaRoo\SnapshotTesting\Wildcard\DateTimeWildcard;
use KigaRoo\SnapshotTesting\Wildcard\IntegerWildcard;
use KigaRoo\SnapshotTesting\Wildcard\UuidWildcard;
use PHPUnit\Framework\TestCase;
use function json_encode;

class JsonTest extends TestCase
{
    use MatchesSnapshots;

    public function testJson() : void
    {
        $data = json_encode(
            [
                'tests' =>
             [
                 'id' => 'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1',
                 'multiple' => [
                     0 => 'a',
                     1 => 6665,
                     2 => 'c',
                 ],
                 'integers' => [
                     123,
                     1234,
                     12345,
                     123456,
                 ],
                 'objects' => [
                     ['id' => 123],
                     ['id' => 234],
                     ['id' => 345],
                 ],
                 'arrays' => [
                     [1,2,3],
                     [3,4,5],
                 ],
                 'dateTimes' => [
                     [
                         '2019-07-10T12:50:20+00:00',
                         '2019-02-28T13:50:24+02:00',
                     ],
                     [
                         '02.02.2012 12:12',
                         '01.01.2011 11:11',
                     ],
                 ],
             ],
            ]
        );

        $wildcards = [
            new UuidWildcard('tests.id'),
            new IntegerWildcard('tests.multiple[1]'),
            new IntegerWildcard('tests.integers[*]'),
            new IntegerWildcard('tests.objects[*].id'),
            new IntegerWildcard('tests.arrays[*][2]'),
            new DateTimeWildcard('tests.dateTimes[0][*]'),
            new DateTimeWildcard('tests.dateTimes[1][*]', 'd.m.Y H:i'),
        ];

        $this->assertMatchesJsonSnapshot($data, $wildcards);
    }
}
