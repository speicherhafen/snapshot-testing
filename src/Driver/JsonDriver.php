<?php

declare(strict_types=1);

namespace KigaRoo\Driver;

use KigaRoo\Exception\ConstraintPathException;
use PHPUnit\Framework\Assert;
use KigaRoo\Driver;
use KigaRoo\Constraint\Constraint;
use KigaRoo\Exception\CantBeSerialized;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class JsonDriver implements Driver
{
    public function serialize(string $json, ?array $fieldConstraints = null): string
    {
        $data = $this->decode($json);

        if(is_array($fieldConstraints))
        {
            $data = $this->replaceFieldsWithConstraintExpression($data, $fieldConstraints);
        }
        
        return json_encode($data, JSON_PRETTY_PRINT).PHP_EOL;
    }

    public function extension(): string
    {
        return 'json';
    }

    public function match(string $expected, string $actual, ?array $fieldConstraints = null)
    {
        if(is_array($fieldConstraints))
        {
            $actualArray = $this->decode($actual);
            $actualArray = $this->replaceFieldsWithConstraintExpression($actualArray, $fieldConstraints);
            $actual = json_encode($actualArray);
        }
        Assert::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @param Constraint[] $fieldConstraints
     * @param string $actual
     */
    private function replaceFieldsWithConstraintExpression(array $actual, array $fieldConstraints) {

        
        foreach($fieldConstraints as $fieldConstraint)
        {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            try {
                $value = $propertyAccessor->getValue($actual, $fieldConstraint->getPath());
            }
            catch (NoSuchPropertyException $exception)
            {
                throw new ConstraintPathException($fieldConstraint->getPath());
            }
            
            //@todo: replace data with constraint->toString()
        }
    }
    
    private function decode(string $data): array
    {
        $data = json_decode($data, true);

        if(false === $data)
        {
            throw new CantBeSerialized('not a valid json string.');
        }
        
        return $data;
    }
}
