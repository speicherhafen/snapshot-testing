<?php

declare(strict_types=1);

namespace KigaRoo;

use KigaRoo\Replacement\Replacement;

interface Driver
{
    /**
     * Serialize a snapshot's data to a string that can be written to a
     * generated snapshot file.
     * 
     * @param  string        $decodedJson
     * @param  Replacement[] $replacements 
     * @return string
     */
    public function serialize(string $decodedJson, array $replacements = []): string;

    /**
     * The extension that should be used to save the snapshot file, without
     * a leading dot.
     */
    public function extension(): string;

    /**
     * Match an expectation with a snapshot's actual contents. Should throw an
     * `ExpectationFailedException` if it doesn't match. This happens by
     * default if you're using PHPUnit's `Assert` class for the match.
     */
    public function match(string $expected, string $actual, array $replacements = []): void;
}
