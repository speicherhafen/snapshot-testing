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
    protected $snapshotIncrementor;

    /** 
     * @var string[] 
     */
    protected $snapshotChanges;

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
    public function markTestIncompleteIfSnapshotsHaveChanged():?  string
    {
        if (empty($this->snapshotChanges)) {
            return null;
        }

        if (count($this->snapshotChanges) === 1) {
            $this->markTestIncomplete($this->snapshotChanges[0]);

            return null;
        }

        $formattedMessages = implode(PHP_EOL, array_map(function (string $message) {
            return "- {$message}";
        }, $this->snapshotChanges));

        Assert::markTestIncomplete($formattedMessages);
    }

    public function assertMatchesJsonSnapshot(string $actual, ?array $fieldConstraints = null): void
    {
        $this->doSnapshotAssertion($actual, new JsonDriver(), $fieldConstraints);
    }

    /**
     * Determines the snapshot's id. By default, the test case's class and
     * method names are used.
     */
    protected function getSnapshotId(): string
    {
        return (new ReflectionClass($this))->getShortName() . '__' .
            $this->getName() . '__' .
            $this->snapshotIncrementor;
    }

    /**
     * Determines the directory where snapshots are stored. By default a
     * `__snapshots__` directory is created at the same level as the test
     * class.
     */
    protected function getSnapshotDirectory(): string
    {
        return dirname((new ReflectionClass($this))->getFileName()) .
            DIRECTORY_SEPARATOR .
            '__snapshots__';
    }

    /**
     * Determines whether or not the snapshot should be updated instead of
     * matched.
     *
     * Override this method it you want to use a different flag or mechanism
     * than `-d --update-snapshots`.
     */
    protected function shouldUpdateSnapshots(): bool
    {
        return in_array('--update-snapshots', $_SERVER['argv'], true);
    }

    protected function doSnapshotAssertion(string $actual, Driver $driver, ?array $fieldConstraints = null): void
    {
        $this->snapshotIncrementor++;

        $snapshot = Snapshot::forTestCase(
            $this->getSnapshotId(),
            $this->getSnapshotDirectory(),
            $driver
        );

        if (! $snapshot->exists()) {
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

    protected function createSnapshotAndMarkTestIncomplete(Snapshot $snapshot, string $actual, ?array $fieldConstraints = null): void
    {
        $snapshot->create($actual, $fieldConstraints);

        $this->registerSnapshotChange("Snapshot created for {$snapshot->id()}");
    }

    protected function updateSnapshotAndMarkTestIncomplete(Snapshot $snapshot, string $actual, ?array $fieldConstraints = null): void
    {
        $snapshot->create($actual, $fieldConstraints);

        $this->registerSnapshotChange("Snapshot updated for {$snapshot->id()}");
    }

    protected function rethrowExpectationFailedExceptionWithUpdateSnapshotsPrompt(ExpectationFailedException $exception): void
    {
        $newMessage = $exception->getMessage() . "\n\n" .
            'Snapshots can be updated by passing ' .
            '`-d --update-snapshots` through PHPUnit\'s CLI arguments.';

        $exceptionReflection = new ReflectionObject($exception);

        $messageReflection = $exceptionReflection->getProperty('message');
        $messageReflection->setAccessible(true);
        $messageReflection->setValue($exception, $newMessage);

        throw $exception;
    }

    protected function registerSnapshotChange(string $message): void
    {
        $this->snapshotChanges[] = $message;
    }
}
