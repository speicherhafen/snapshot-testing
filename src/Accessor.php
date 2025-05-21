<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting;

use KigaRoo\SnapshotTesting\Exception\InvalidMappingPath;
use KigaRoo\SnapshotTesting\Exception\WildcardMismatch;
use KigaRoo\SnapshotTesting\Wildcard\Wildcard;
use stdClass;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use function explode;
use function get_class;
use function is_array;
use function is_string;
use function sprintf;

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

        $dataPaths = $this->buildDataPaths($wildcard, $data);

        foreach ($dataPaths as $path => $data) {
            $this->assert($wildcard, $data);
        }
    }

    /**
     * @param mixed $data
     *
     * @return mixed[]
     */
    private function buildDataPaths(Wildcard $wildcard, $data) : array
    {
        $paths     = explode('[*]', $wildcard->atPath());
        $dataPaths = ['' => $data];
        foreach ($paths as $k => $path) {
            foreach ($dataPaths as $checkPath => $pathData) {
                if ('' === $checkPath . $path) {
                    $elements = $pathData;
                } else {
                    $elements = $this->getValue($data, $checkPath . $path);
                }
                unset($dataPaths[$checkPath]);
                if (is_array($elements)) {
                    foreach ($elements as $n => $element) {
                        $dataPaths[sprintf('%s%s[%s]', $checkPath, $path, $n)] = $element;
                    }
                } elseif (!is_null($elements)) {
                    $finalPath             = sprintf('%s%s', $checkPath, $path);
                    $dataPaths[$finalPath] = $this->getValue($data, $finalPath);
                }
            }
        }

        return $dataPaths;
    }

    /**
     * @param string|stdClass|mixed[] $data
     *
     * @throws InvalidMappingPath
     */
    public function replaceFields(&$data, Wildcard $wildcard) : void
    {
        if (is_string($data)) {
            return;
        }

        $dataPaths        = $this->buildDataPaths($wildcard, $data);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($dataPaths as $path => $pathData) {
            $propertyAccessor->setValue($data, $path, Wildcard::REPLACEMENT);
        }
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
            throw new WildcardMismatch(get_class($wildcard), $wildcard->atPath(), $value);
        }
    }
}
