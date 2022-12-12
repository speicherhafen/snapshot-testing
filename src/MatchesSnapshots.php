<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting;

use KigaRoo\SnapshotTesting\Driver\CsvDriver;
use KigaRoo\SnapshotTesting\Driver\JsonDriver;
use KigaRoo\SnapshotTesting\Driver\XmlDriver;
use KigaRoo\SnapshotTesting\Wildcard\Wildcard;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use ReflectionClass;
use ReflectionObject;
use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use function array_map;
use function count;
use function dirname;
use function implode;
use function in_array;
use function sprintf;

trait MatchesSnapshots
{
    /** @var int */
    private $snapshotIncrementer;

    /** @var string[] */
    private $snapshotChanges = [];

    /**
     * @before
     */
    public function setUpSnapshotIncrementer() : void
    {
        $this->snapshotIncrementer = 0;
    }

    /**
     * @after
     */
    public function markTestIncompleteIfSnapshotsHaveChanged() : void
    {
        if (count($this->snapshotChanges) === 0) {
            return;
        }

        if (count($this->snapshotChanges) === 1) {
            Assert::markTestIncomplete($this->snapshotChanges[0]);

            return;
        }

        $formattedMessages = array_map(
            static function (string $message) {
                return '- ' . $message;
            },
            $this->snapshotChanges
        );

        Assert::markTestIncomplete(implode(PHP_EOL, $formattedMessages));
    }

    /**
     * @param Wildcard[] $wildcards
     */
    public function assertMatchesJsonSnapshot(string $actual, array $wildcards = []) : void
    {
        $this->doSnapshotAssertion($actual, new JsonDriver(), $wildcards);
    }

    /**
     * @param Wildcard[] $wildcards
     */
    public function assertMatchesXmlSnapshot(string $actual, array $wildcards = []) : void
    {
        $this->doSnapshotAssertion($actual, new XmlDriver(), $wildcards);
    }
    
    /**
     * @param Wildcard[] $wildcards
     */
    public function assertMatchesCsvSnapshot(string $actual, array $wildcards = [], string $fieldSeparator = ';', string $fieldEnclosure = '"') : void
    {
        $this->doSnapshotAssertion($actual, new CsvDriver($fieldSeparator, $fieldEnclosure), $wildcards);
    }

    /**
     * Determines whether or not the snapshot should be created instead of
     * matched.
     *
     * Override this method if you want to use a different flag or mechanism
     * than `-d --without-creating-snapshots`.
     */
    protected function shouldCreateSnapshots() : bool
    {
        return ! in_array('--without-creating-snapshots', $_SERVER['argv'], true);
    }

    abstract public function getName(bool $withDataSet = true): string;

    private function getSnapshotId(): string
    {
        return sprintf(
            '%s__%s__%s',
            (new ReflectionClass($this))->getShortName(),
            $this->getName(),
            $this->snapshotIncrementer
        );
    }

    private function getSnapshotDirectory(): string
    {
        $directoryName = dirname((new ReflectionClass($this))->getFileName());

        return sprintf('%s%s__snapshots__', $directoryName, DIRECTORY_SEPARATOR);
    }

    /**
     * Determines whether or not the snapshot should be updated instead of
     * matched.
     *
     * Override this method it you want to use a different flag or mechanism
     * than `-d --update-snapshots`.
     */
    private function shouldUpdateSnapshots(): bool
    {
        return in_array('--update-snapshots', $_SERVER['argv'], true);
    }

    /**
     * @param Wildcard[] $wildcards
     */
    private function doSnapshotAssertion(string $actual, Driver $driver, array $wildcards = []): void
    {
        $this->snapshotIncrementer++;

        $filesystem = new Filesystem($this->getSnapshotDirectory());
        $snapshotHandler = new SnapshotHandler($filesystem);

        $filename = $snapshotHandler->getFilename($this->getSnapshotId(), $driver);
        $content = '';
        if ($filesystem->has($filename)) {
            $content = $filesystem->read($filename);
        }

        $snapshot = Snapshot::forTestCase(
            $this->getSnapshotId(),
            $content,
            $driver
        );

        if (!$snapshotHandler->snapshotExists($snapshot)) {
            $this->assertSnapshotShouldBeCreated($filename);

            $this->createSnapshotAndMarkTestIncomplete($snapshot, $actual);
        }

        if ($this->shouldUpdateSnapshots()) {
            try {
                // We only want to update snapshots which need updating. If the snapshot doesn't
                // match the expected output, we'll catch the failure, create a new snapshot and
                // mark the test as incomplete.
                $snapshot->assertMatches($actual, $wildcards);
            } catch (ExpectationFailedException $exception) {
                $this->updateSnapshotAndMarkTestIncomplete($snapshot, $actual);
            }
        }

        try {
            $snapshot->assertMatches($actual, $wildcards);
        } catch (ExpectationFailedException $exception) {
            $this->rethrowExpectationFailedExceptionWithUpdateSnapshotsPrompt($exception);
        }
    }

    private function createSnapshotAndMarkTestIncomplete(Snapshot $snapshot, string $actual): void
    {
        $snapshot->update($actual);

        $snapshotFactory = new SnapshotHandler(new Filesystem($this->getSnapshotDirectory()));
        $snapshotFactory->writeToFilesystem($snapshot);

        $this->registerSnapshotChange(sprintf('Snapshot created for %s', $snapshot->getId()));
    }

    private function updateSnapshotAndMarkTestIncomplete(Snapshot $snapshot, string $actual): void
    {
        $snapshot->update($actual);

        $snapshotFactory = new SnapshotHandler(new Filesystem($this->getSnapshotDirectory()));
        $snapshotFactory->writeToFilesystem($snapshot);

        $this->registerSnapshotChange(sprintf('Snapshot updated for %s', $snapshot->getId()));
    }

    private function rethrowExpectationFailedExceptionWithUpdateSnapshotsPrompt(
        ExpectationFailedException $exception
    ): void {
        $newMessage = sprintf(
            '%s%s%s',
            $exception->getMessage(),
            PHP_EOL . PHP_EOL,
            'Snapshots can be updated by passing `-d --update-snapshots` through PHPUnit\'s CLI arguments.'
        );

        $exceptionReflection = new ReflectionObject($exception);

        $messageReflection = $exceptionReflection->getProperty('message');
        $messageReflection->setAccessible(true);
        $messageReflection->setValue($exception, $newMessage);

        throw $exception;
    }

    private function registerSnapshotChange(string $message): void
    {
        $this->snapshotChanges[] = $message;
    }

    protected function assertSnapshotShouldBeCreated(string $snapshotFileName): void
    {
        if ($this->shouldCreateSnapshots()) {
            return;
        }

        $this->fail(
            "Snapshot \"$snapshotFileName\" does not exist.\n".
            'You can automatically create it by removing '.
            '`-d --without-creating-snapshots` of PHPUnit\'s CLI arguments.'
        );
    }
}
