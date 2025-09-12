<?php

namespace Imanghafoori\DatabaseFresh;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\Traits\CanConfigureMigrationCommands;
use Illuminate\Support\LazyCollection;

trait FastRefreshDatabase
{
    use CanConfigureMigrationCommands;

    /**
     * PHPUnit hook to start watching insert queries before each test.
     *
     * @before
     */
    public function setupDatabaseAndStartWatchingTables(): void
    {
        $database = $this->app->make('db');
        collect($this->connectionsToUnseed())->each(
            fn ($name) => $database->connection($name)->enableQueryLog()
        );

        $this->beforeApplicationDestroyed(function () {
            $this->unseedTablesForAllConnections();
        });
    }

    /**
     * Truncate the database tables for all configured connections.
     */
    protected function unseedTablesForAllConnections(): void
    {
        $database = $this->app->make('db');

        collect($this->connectionsToUnseed())
            ->each(function ($name) use ($database) {
                $connection = $database->connection($name);

                $connection->getSchemaBuilder()->disableForeignKeyConstraints();
                $this->unseedTablesForConnection($connection, $name);
                $connection->getSchemaBuilder()->enableForeignKeyConstraints();
            });
    }

    /**
     * Truncate the database tables for the given database connection.
     */
    protected function unseedTablesForConnection(ConnectionInterface $connection, ?string $name): void
    {
        $dispatcher = $connection->getEventDispatcher();

        $connection->unsetEventDispatcher();

        $this->getInsertedTables($connection)
            ->each(fn ($table) => $connection->table($this->withoutTablePrefix($connection, $table))->truncate());

        $connection->setEventDispatcher($dispatcher);
    }

    /**
     * Get a list of table names that have been subject to insert queries.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    protected function getInsertedTables($connection)
    {
        return LazyCollection::make($connection->getQueryLog())
            ->map(fn ($log) => $log['query'])
            ->filter(fn ($query) => str_starts_with(strtolower($query), 'insert'))
            ->map(function ($query) {
                preg_match($this->getRegex(), $query, $match);

                return $match[1] ?? null; // <== table name
            })->filter()->unique();
    }

    /**
     * Get the regex need to derive the table name from an insert query
     */
    protected function getRegex(): string
    {
        return '/^insert(?:\s+?)(?:ignore )?into (?:\s+?)?(?:\`|\[|\"|\')?(.*?)(?:\`|\]|\"|\')? .*/i';
    }

    /**
     * Remove the table prefix from a table name, if it exists.
     *
     * @return string
     */
    protected function withoutTablePrefix(ConnectionInterface $connection, string $table)
    {
        $prefix = $connection->getTablePrefix();

        return str_starts_with($table, $prefix)
            ? substr($table, strlen($prefix))
            : $table;
    }

    /**
     * The database connections that should have their tables truncated.
     */
    protected function connectionsToUnseed(): array
    {
        return property_exists($this, 'connectionsToUnseed')
            ? $this->connectionsToUnseed : [null];
    }
}
