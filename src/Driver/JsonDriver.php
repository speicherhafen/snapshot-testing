<?php

declare(strict_types=1);

namespace KigaRoo\Driver;

use KigaRoo\Accessor;
use KigaRoo\Replacement\Replacement;
use PHPUnit\Framework\Assert;
use KigaRoo\Driver;
use KigaRoo\Exception\CantBeSerialized;

final class JsonDriver implements Driver
{
    public function serialize(string $json, array $replacements = []): string
    {
        $data = $this->decode($json);

        $data = $this->replaceFieldsWithConstraintExpression($data, $replacements);

        return json_encode($data, JSON_PRETTY_PRINT).PHP_EOL;
    }

    public function extension(): string
    {
        return 'json';
    }

    public function match(string $expected, string $actual, array $replacements = []): void
    {
        $actualArray = $this->decode($actual);
        $actualArray = $this->replaceFieldsWithConstraintExpression($actualArray, $replacements);
        $actual = json_encode($actualArray);

        Assert::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @param  $actualStringOrObjectOrArray string|\stdClass|array
     * @param  Replacement[]                                      $replacements
     * @return string|\stdClass|array
     */
    private function replaceFieldsWithConstraintExpression($actualStringOrObjectOrArray, array $replacements)
    {

        if(is_string($actualStringOrObjectOrArray)) {
            return $actualStringOrObjectOrArray;
        }
        
        foreach($replacements as $replacement)
        {
            (new Accessor)->replace($actualStringOrObjectOrArray, $replacement);
        }
        
        return $actualStringOrObjectOrArray;
    }

    /**
     * @param  string $data
     * @return string|object|array
     * @throws CantBeSerialized
     */
    private function decode(string $data) 
    {
        $data = json_decode($data);

        if(false === $data) {
            throw new CantBeSerialized('Given string does not contain valid json.');
        }

        return $data;
    }
}
