<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting;

use KigaRoo\SnapshotTesting\Exception\CantBeReplaced;
use KigaRoo\SnapshotTesting\Exception\InvalidMappingPath;
use KigaRoo\SnapshotTesting\Replacement\Replacement;
use stdClass;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use function count;
use function explode;
use function get_class;
use function is_string;
use function mb_substr;
use function preg_match;

final class Accessor
{
    /**
     * @param string|stdClass|mixed[] $actualStringOrObjectOrArray
     *
     * @return string|stdClass|mixed[]
     *
     * @throws InvalidMappingPath
     */
    public function replace($actualStringOrObjectOrArray, Replacement $replacement)
    {
        if (is_string($actualStringOrObjectOrArray)) {
            return $actualStringOrObjectOrArray;
        }

        $paths = explode('[*]', $replacement->atPath());

        if (count($paths) > 2) {
            throw new InvalidMappingPath($replacement->atPath());
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if (count($paths) === 1) {
            $this->assert($replacement, $this->getValue($actualStringOrObjectOrArray, $replacement->atPath()));

            $propertyAccessor->setValue($actualStringOrObjectOrArray, $replacement->atPath(), $replacement->getValue());

            return $actualStringOrObjectOrArray;
        }

        $elements         = $propertyAccessor->getValue($actualStringOrObjectOrArray, $paths[0]);
        $modifiedElements = [];

        foreach ($elements as $element) {
            if ($paths[1] === '') {
                $this->assert($replacement, $element);
                $element = $replacement->getValue();
            } elseif ($paths[1]{0 === '.'}) {
                $subPath = mb_substr($paths[1], 1);
                $this->assert($replacement, $this->getValue($element, $subPath));
                $propertyAccessor->setValue($element, $subPath, $replacement->getValue());
            } elseif (preg_match('#^\[[0-9]+\]#', $paths[1])) {
                $this->assert($replacement, $this->getValue($element, $paths[1]));
                $propertyAccessor->setValue($element, $paths[1], $replacement->getValue());
            } else {
                throw new InvalidMappingPath($replacement->atPath());
            }
            $modifiedElements[] = $element;
        }
        $propertyAccessor->setValue($actualStringOrObjectOrArray, $paths[0], $modifiedElements);

        return $actualStringOrObjectOrArray;
    }

    /**
     * @param string|stdClass|mixed[] $data
     *
     * @return string|stdClass|mixed[]
     *
     * @throws InvalidMappingPath
     */
    private function getValue($data, string $path)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        try {
            return $propertyAccessor->getValue($data, $path);
        } catch (NoSuchPropertyException $exception) {
            throw new InvalidMappingPath($path);
        }
    }

    /**
     * @param stdClass|mixed[] $value
     *
     * @throws CantBeReplaced
     */
    private function assert(Replacement $replacement, $value) : void
    {
        if (! $replacement->match($value)) {
            throw new CantBeReplaced(get_class($replacement), $replacement->atPath());
        }
    }
}
