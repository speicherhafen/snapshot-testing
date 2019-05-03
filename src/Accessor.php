<?php

declare(strict_types=1);

namespace KigaRoo;

use KigaRoo\Exception\CantBeReplaced;
use KigaRoo\Exception\InvalidConstraintPath;
use KigaRoo\Replacement\Replacement;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class Accessor
{

    public function replace($actualStringOrObjectOrArray, Replacement $replacement)
    {
        if(is_string($actualStringOrObjectOrArray)) {
            return $actualStringOrObjectOrArray;
        }

        $paths = explode('[*]', $replacement->atPath());

        if(count($paths) > 2) {
            throw new \Exception('adsasda');
        }
        
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        
        if(1 === count($paths)) {
            
            $this->assert($replacement, $this->getValue($actualStringOrObjectOrArray, $replacement->atPath()));

            $propertyAccessor->setValue($actualStringOrObjectOrArray, $replacement->atPath(), $replacement->getValue());
            
            return $actualStringOrObjectOrArray;
        }

        $elements = $propertyAccessor->getValue($actualStringOrObjectOrArray, $paths[0]);
        $modifiedElements = [];

        foreach($elements as $element)
        {
            $this->assert($replacement, $this->getValue($element, $paths[1]));
            
            $propertyAccessor->setValue($element, $paths[1], $replacement->getValue());
            $modifiedElements[] = $element;

        }
        $propertyAccessor->setValue($actualStringOrObjectOrArray, $paths[0], $modifiedElements);

        return $actualStringOrObjectOrArray;
    }

    /**
     * @param  $data
     * @param  string $path
     * @return \stdClass|array
     * @throws InvalidConstraintPath
     */
    private function getValue($data, string $path)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        
        try {
            return $propertyAccessor->getValue($data, $path);
        }
        catch (NoSuchPropertyException $exception)
        {
            throw new InvalidConstraintPath($path());
        }
    }

    /**
     * @param  Replacement     $replacement
     * @param  \stdClass|array $value
     * @throws CantBeReplaced
     */
    private function assert(Replacement $replacement, $value): void
    {
        if(!$replacement->match($value)) {
            throw new CantBeReplaced(get_class($replacement), $replacement->atPath());
        }
    }
        

}
