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
     * @param string|stdClass|mixed[] $data
     *
     * @throws InvalidMappingPath
     */
    public function assertFields($data, Replacement $replacement) : void
    {
        if (is_string($data)) {
            return;
        }

        $paths = explode('[*]', $replacement->atPath());
        if (count($paths) > 2) {
            throw new InvalidMappingPath($replacement->atPath());
        }

        if (count($paths) === 1) {
            $this->assert($replacement, $this->getValue($data, $replacement->atPath()));

            return;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $elements         = $propertyAccessor->getValue($data, $paths[0]);
        foreach ($elements as $element) {
            $substring = $paths[1];
            if ($substring === '') {
                $this->assert($replacement, $element);
            } elseif ($substring[0] === '.') {
                $subPath = mb_substr($substring, 1);
                $this->assert($replacement, $this->getValue($element, $subPath));
            } elseif (preg_match('#^\[[0-9]+\]#', $substring)) {
                $this->assert($replacement, $this->getValue($element, $substring));
            } else {
                throw new InvalidMappingPath($replacement->atPath());
            }
        }
    }

    /**
     * @param string|stdClass|mixed[] $data
     *
     * @throws InvalidMappingPath
     */
    public function replaceFields($data, Replacement $replacement) : void
    {
        if (is_string($data)) {
            return;
        }

        $paths = explode('[*]', $replacement->atPath());

        if (count($paths) > 2) {
            throw new InvalidMappingPath($replacement->atPath());
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if (count($paths) === 1) {
            $propertyAccessor->setValue($data, $replacement->atPath(), Replacement::VALUE);

            return;
        }

        $elements         = $propertyAccessor->getValue($data, $paths[0]);
        $modifiedElements = [];

        foreach ($elements as $element) {
            $substring = $paths[1];
            if ($substring === '') {
                $element = Replacement::VALUE;
            } elseif ($substring[0] === '.') {
                $subPath = mb_substr($substring, 1);
                $propertyAccessor->setValue($element, $subPath, Replacement::VALUE);
            } elseif (preg_match('#^\[[0-9]+\]#', $substring)) {
                $propertyAccessor->setValue($element, $substring, Replacement::VALUE);
            } else {
                throw new InvalidMappingPath($replacement->atPath());
            }
            $modifiedElements[] = $element;
        }

        $propertyAccessor->setValue($data, $paths[0], $modifiedElements);
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
