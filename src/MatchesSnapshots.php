<?php

declare(strict_types=1);

namespace KigaRoo;

use PHPUnit\Framework\ExpectationFailedException;
use ReflectionClass;
use ReflectionObject;
use KigaRoo\Drivers\JsonDriver;

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

        $this->markTestIncomplete($formattedMessages);
    }

    public function assertMatchesJsonSnapshot(string $actual): void
    {
        $this->doSnapshotAssertion($actual, new JsonDriver());
    }

    /**
     * Determines the snapshot's id. By default, the test case's class and
     * method names are used.
     *
     * @return string
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
     *
     * @return string
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
     *
     * @return bool
     */
    protected function shouldUpdateSnapshots(): bool
    {
        return in_array('--update-snapshots', $_SERVER['argv'], true);
    }

    protected function doSnapshotAssertion(string $actual, Driver $driver): void
    {
        $this->snapshotIncrementor++;

        $snapshot = Snapshot::forTestCase(
            $this->getSnapshotId(),
            $this->getSnapshotDirectory(),
            $driver
        );

        if (! $snapshot->exists()) {
            $this->createSnapshotAndMarkTestIncomplete($snapshot, $actual);
        }

        if ($this->shouldUpdateSnapshots()) {
            try {
                // We only want to update snapshots which need updating. If the snapshot doesn't
                // match the expected output, we'll catch the failure, create a new snapshot and
                // mark the test as incomplete.
                $snapshot->assertMatches($actual);
            } catch (ExpectationFailedException $exception) {
                $this->updateSnapshotAndMarkTestIncomplete($snapshot, $actual);
            }
        }

        try {
            $snapshot->assertMatches($actual);
        } catch (ExpectationFailedException $exception) {
            $this->rethrowExpectationFailedExceptionWithUpdateSnapshotsPrompt($exception);
        }
    }

    protected function createSnapshotAndMarkTestIncomplete(Snapshot $snapshot, string $actual): void
    {
        $snapshot->create($actual);

        $this->registerSnapshotChange("Snapshot created for {$snapshot->id()}");
    }

    protected function updateSnapshotAndMarkTestIncomplete(Snapshot $snapshot, string $actual): void
    {
        $snapshot->create($actual);

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
