<?php

declare(strict_types=1);

namespace KigaRoo\Driver;

use KigaRoo\Replacement\Replacement;
use KigaRoo\Exception\CannotBeReplaced;
use KigaRoo\Exception\InvalidConstraintPath;
use PHPUnit\Framework\Assert;
use KigaRoo\Driver;
use KigaRoo\Exception\CantBeSerialized;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class JsonDriver implements Driver
{
    public function serialize(string $json, array $fieldConstraints = []): string
    {
        $data = $this->decode($json);

        $data = $this->replaceFieldsWithConstraintExpression($data, $fieldConstraints);

        return json_encode($data, JSON_PRETTY_PRINT).PHP_EOL;
    }

    public function extension(): string
    {
        return 'json';
    }

    public function match(string $expected, string $actual, array $fieldConstraints = []): void
    {
        $actualArray = $this->decode($actual);
        $actualArray = $this->replaceFieldsWithConstraintExpression($actualArray, $fieldConstraints);
        $actual = json_encode($actualArray);

        Assert::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @param $actualStringOrObjectOrArray string|\stdClass|array
     * @param Replacement[] $fieldConstraints
     * @return string|\stdClass|array
     * @throws CannotBeReplaced
     * @throws InvalidConstraintPath
     */
    private function replaceFieldsWithConstraintExpression($actualStringOrObjectOrArray, array $fieldConstraints)
    {

        if(is_string($actualStringOrObjectOrArray)) {
            return $actualStringOrObjectOrArray;
        }
        
        foreach($fieldConstraints as $fieldConstraint)
        {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            try {
                $value = $propertyAccessor->getValue($actualStringOrObjectOrArray, $fieldConstraint->atPath());
            }
            catch (NoSuchPropertyException $exception)
            {
                throw new InvalidConstraintPath($fieldConstraint->atPath());
            }
            
            if(!$fieldConstraint->match($value)) {
                throw new CannotBeReplaced(get_class($fieldConstraint), $fieldConstraint->atPath());
            }
            $propertyAccessor->setValue($actualStringOrObjectOrArray, $fieldConstraint->atPath(), $fieldConstraint->getValue());
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
            throw new CantBeSerialized('not a valid json string.');
        }

        return $data;
    }
}
