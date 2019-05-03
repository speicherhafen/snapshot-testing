<?php

declare(strict_types=1);

namespace KigaRoo;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use ReflectionClass;
use ReflectionObject;
use KigaRoo\Driver\JsonDriver;

trait MatchesSnapshots
{
    /**
     * @var int
     */
    private $snapshotIncrementor;

    /**
     * @var string[]
     */
    private $snapshotChanges;

    /**
     * @before
     */
    public function setUpSnapshotIncrementor(): void
    {
        $this->snapshotIncrementor = 0;
    }

    /**
     * @after
     */
    public function markTestIncompleteIfSnapshotsHaveChanged(): void
    {
        if (count($this->snapshotChanges) === 0) {
            return;
        }

        if (count($this->snapshotChanges) === 1) {
            Assert::markTestIncomplete($this->snapshotChanges[0]);

            return;
        }

        $formattedMessages = array_map(static function (string $message) {
            return '- ' . $message;
        }, $this->snapshotChanges);

        Assert::markTestIncomplete(implode(PHP_EOL, $formattedMessages));
    }

    public function assertMatchesJsonSnapshot(string $actual, ?array $fieldConstraints = null): void
    {
        $this->doSnapshotAssertion($actual, new JsonDriver(), $fieldConstraints);
    }

    abstract public function getName($withDataSet = true);

    /**
     * Determines the snapshot's id. By default, the test case's class and
     * method names are used.
     */
    private function getSnapshotId(): string
    {
        return sprintf(
            '%s__%s__%s',
            (new ReflectionClass($this))->getShortName(),
            $this->getName(),
            $this->snapshotIncrementor
        );
    }

    /**
     * Determines the directory where snapshots are stored. By default a
     * `__snapshots__` directory is created at the same level as the test
     * class.
     */
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

    private function doSnapshotAssertion(string $actual, Driver $driver, ?array $fieldConstraints = null): void
    {
        $this->snapshotIncrementor++;

        $snapshot = Snapshot::forTestCase(
            $this->getSnapshotId(),
            $this->getSnapshotDirectory(),
            $driver
        );

        if (!$snapshot->exists()) {
            $this->createSnapshotAndMarkTestIncomplete($snapshot, $actual, $fieldConstraints);
        }

        if ($this->shouldUpdateSnapshots()) {
            try {
                // We only want to update snapshots which need updating. If the snapshot doesn't
                // match the expected output, we'll catch the failure, create a new snapshot and
                // mark the test as incomplete.
                $snapshot->assertMatches($actual, $fieldConstraints);
            } catch (ExpectationFailedException $exception) {
                $this->updateSnapshotAndMarkTestIncomplete($snapshot, $actual, $fieldConstraints);
            }
        }

        try {
            $snapshot->assertMatches($actual, $fieldConstraints);
        } catch (ExpectationFailedException $exception) {
            $this->rethrowExpectationFailedExceptionWithUpdateSnapshotsPrompt($exception);
        }
    }

    private function createSnapshotAndMarkTestIncomplete(Snapshot $snapshot, string $actual, ?array $fieldConstraints = null): void
    {
        $snapshot->create($actual, $fieldConstraints);

        $this->registerSnapshotChange(sprintf('Snapshot created for %s', $snapshot->id()));
    }

    private function updateSnapshotAndMarkTestIncomplete(Snapshot $snapshot, string $actual, ?array $fieldConstraints = null): void
    {
        $snapshot->create($actual, $fieldConstraints);

        $this->registerSnapshotChange(sprintf('Snapshot updated for %s', $snapshot->id()));
    }

    private function rethrowExpectationFailedExceptionWithUpdateSnapshotsPrompt(ExpectationFailedException $exception): void
    {
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
}
