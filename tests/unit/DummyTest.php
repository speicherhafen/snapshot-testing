<?php

declare(strict_types=1);

namespace Tests;

use KigaRoo\Replacement\IntegerReplacement;
use KigaRoo\Replacement\UuidReplacement;
use KigaRoo\MatchesSnapshots;
use PHPUnit\Framework\TestCase;

class DummyTest extends TestCase
{
    use MatchesSnapshots;

    public function testDummy()
    {

        $data = json_encode(
            ['tests' =>
             [
                 'id' => 'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1',
                 'multiple' => [
                     0 => 'a',
                     1 => 6665,
                     2 => 'c',
                 ],
                 'ints' => [
                     123,
                     1234,
                     12345,
                     123456
                 ],
                 'objects' => [
                     ['id' => 123],
                     ['id' => 234],
                     ['id' => 345],
                 ],
                 'arrays' => [
                     [1,2,3],
                     [3,4,5],
                 ]
             ],
            ]
        );
        
        $fieldConstraints = [
            new UuidReplacement('tests.id'),
            new IntegerReplacement('tests.multiple[1]'),
            new IntegerReplacement('tests.ints[*]'),
            new IntegerReplacement('tests.objects[*].id'), // implemented
            new IntegerReplacement('tests.arrays[*][2]'), // implemented
        ];

        $this->assertMatchesJsonSnapshot($data, $fieldConstraints);
    }
}
