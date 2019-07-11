<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting;

use KigaRoo\SnapshotTesting\Exception\InvalidMappingPath;
use KigaRoo\SnapshotTesting\Exception\WildcardMismatch;
use KigaRoo\SnapshotTesting\Wildcard\Wildcard;
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
    public function assertFields($data, Wildcard $wildcard) : void
    {
        if (is_string($data)) {
            return;
        }

        $paths = explode('[*]', $wildcard->atPath());
        if (count($paths) > 2) {
            throw new InvalidMappingPath($wildcard->atPath());
        }

        if (count($paths) === 1) {
            $this->assert($wildcard, $this->getValue($data, $wildcard->atPath()));

            return;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $elements         = $propertyAccessor->getValue($data, $paths[0]);
        foreach ($elements as $element) {
            $substring = $paths[1];
            if ($substring === '') {
                $this->assert($wildcard, $element);
            } elseif ($substring[0] === '.') {
                $subPath = mb_substr($substring, 1);
                $this->assert($wildcard, $this->getValue($element, $subPath));
            } elseif (preg_match('#^\[[0-9]+\]#', $substring)) {
                $this->assert($wildcard, $this->getValue($element, $substring));
            } else {
                throw new InvalidMappingPath($wildcard->atPath());
            }
        }
    }

    /**
     * @param string|stdClass|mixed[] $data
     *
     * @throws InvalidMappingPath
     */
    public function replaceFields($data, Wildcard $wildcard) : void
    {
        if (is_string($data)) {
            return;
        }

        $paths = explode('[*]', $wildcard->atPath());

        if (count($paths) > 2) {
            throw new InvalidMappingPath($wildcard->atPath());
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if (count($paths) === 1) {
            $propertyAccessor->setValue($data, $wildcard->atPath(), Wildcard::REPLACEMENT);

            return;
        }

        $elements         = $propertyAccessor->getValue($data, $paths[0]);
        $modifiedElements = [];

        foreach ($elements as $element) {
            $substring = $paths[1];
            if ($substring === '') {
                $element = Wildcard::REPLACEMENT;
            } elseif ($substring[0] === '.') {
                $subPath = mb_substr($substring, 1);
                $propertyAccessor->setValue($element, $subPath, Wildcard::REPLACEMENT);
            } elseif (preg_match('#^\[[0-9]+\]#', $substring)) {
                $propertyAccessor->setValue($element, $substring, Wildcard::REPLACEMENT);
            } else {
                throw new InvalidMappingPath($wildcard->atPath());
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
     * @throws WildcardMismatch
     */
    private function assert(Wildcard $wildcard, $value) : void
    {
        if (! $wildcard->match($value)) {
            throw new WildcardMismatch(get_class($wildcard), $wildcard->atPath());
        }
    }
}
