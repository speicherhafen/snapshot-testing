<?php

declare(strict_types=1);

namespace KigaRoo;

interface Driver
{
    /**
     * Serialize a snapshot's data to a string that can be written to a
     * generated snapshot file.
     */
    public function serialize(string $decodedJson, array $fieldConstraints = []): string;

    /**
     * The extension that should be used to save the snapshot file, without
     * a leading dot.
     */
    public function extension(): string;

    /**
     * Match an expectation with a snapshot's actual contents. Should throw an
     * `ExpectationFailedException` if it doesn't match. This happens by
     * default if you're using PHPUnit's `Assert` class for the match.
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function match(string $expected, string $actual, array $fieldConstraints = []);
}
