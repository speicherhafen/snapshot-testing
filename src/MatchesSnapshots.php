<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting;

use KigaRoo\SnapshotTesting\Driver\JsonDriver;
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
    private $snapshotIncrementor;

    /** @var string[] */
    private $snapshotChanges = [];

    /**
     * @before
     */
    public function setUpSnapshotIncrementor() : void
    {
        $this->snapshotIncrementor = 0;
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
     * // phpcs:disable
     * @param bool $withDataSet
     *
     * @return string
     * // phpcs:disable
     */
    abstract public function getName($withDataSet = true);

    private function getSnapshotId() : string
    {
        return sprintf(
            '%s__%s__%s',
            (new ReflectionClass($this))->getShortName(),
            $this->getName(),
            $this->snapshotIncrementor
        );
    }

    private function getSnapshotDirectory() : string
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
    private function shouldUpdateSnapshots() : bool
    {
        return in_array('--update-snapshots', $_SERVER['argv'], true);
    }

    /**
     * @param Wildcard[] $wildcards
     */
    private function doSnapshotAssertion(string $actual, Driver $driver, array $wildcards = []) : void
    {
        $this->snapshotIncrementor++;

        $filesystem      = new Filesystem($this->getSnapshotDirectory());
        $snapshotHandler = new SnapshotHandler($filesystem);

        $filename = $snapshotHandler->getFilename($this->getSnapshotId(), $driver);
        $content  = '';
        if ($filesystem->has($filename)) {
            $content = $filesystem->read($filename);
        }

        $snapshot = Snapshot::forTestCase(
            $this->getSnapshotId(),
            $content,
            $driver
        );

        if (! $snapshotHandler->snapshotExists($snapshot)) {
            $this->createSnapshotAndMarkTestIncomplete($snapshot, $actual, $wildcards);
        }

        if ($this->shouldUpdateSnapshots()) {
            try {
                // We only want to update snapshots which need updating. If the snapshot doesn't
                // match the expected output, we'll catch the failure, create a new snapshot and
                // mark the test as incomplete.
                $snapshot->assertMatches($actual, $wildcards);
            } catch (ExpectationFailedException $exception) {
                $this->updateSnapshotAndMarkTestIncomplete($snapshot, $actual, $wildcards);
            }
        }

        try {
            $snapshot->assertMatches($actual, $wildcards);
        } catch (ExpectationFailedException $exception) {
            $this->rethrowExpectationFailedExceptionWithUpdateSnapshotsPrompt($exception);
        }
    }

    /**
     * @param Wildcard[] $wildcards
     */
    private function createSnapshotAndMarkTestIncomplete(
        Snapshot $snapshot,
        string $actual,
        array $wildcards = []
    ) : void {
        $snapshot->update($actual, $wildcards);

        $snapshotFactory = new SnapshotHandler(new Filesystem($this->getSnapshotDirectory()));
        $snapshotFactory->writeToFilesystem($snapshot);

        $this->registerSnapshotChange(sprintf('Snapshot created for %s', $snapshot->getId()));
    }

    /**
     * @param Wildcard[] $wildcards
     */
    private function updateSnapshotAndMarkTestIncomplete(
        Snapshot $snapshot,
        string $actual,
        array $wildcards = []
    ) : void {
        $snapshot->update($actual, $wildcards);

        $snapshotFactory = new SnapshotHandler(new Filesystem($this->getSnapshotDirectory()));
        $snapshotFactory->writeToFilesystem($snapshot);

        $this->registerSnapshotChange(sprintf('Snapshot updated for %s', $snapshot->getId()));
    }

    private function rethrowExpectationFailedExceptionWithUpdateSnapshotsPrompt(
        ExpectationFailedException $exception
    ) : void {
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

    private function registerSnapshotChange(string $message) : void
    {
        $this->snapshotChanges[] = $message;
    }
}
