For large databases with more than 100 tables, it is very slow to drop all the tables and migrate again.
It is also still very slow to run the `truncate` query against all the tables.
The idea of this package is to listen for query logs when the tests start and truncated only the table that are involved with the tests and ignore the rest.

Note that no table gets dropped or migrated. It only runs `truncate table_name` query

You can install:

```bash
composer require imanghafoori/laravel-fast-refresh-database --dev
```


### Usage
Add the trait to your test class. The package automatically starts watching insert queries before each test (via a PHPUnit `@before` hook) and truncates only the tables that were touched after each test:

```php
use Imanghafoori\DatabaseFresh\FastRefreshDatabase;

class MyTest extends TestCase
{
    use FastRefreshDatabase;

    public function test_user_can_run()
    {
        // ... your test code
    }
}

```

Tip: Put the trait on your base `Tests\\TestCase` to enable it for all tests.

### Manual setup (legacy PHPUnit)
If your PHPUnit version does not support `@before`, you can still invoke the setup helper in `setUp()`:

```php
protected function setUp(): void
{
    parent::setUp();

    // Manually start watching inserts for this test process
    $this->setupDatabaseAndStartWatchingTables();
}
```



You may also check my other package as well:

- github.com/imanghafoori1
