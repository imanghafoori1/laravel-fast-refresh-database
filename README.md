For large databases with more than 100 tables, it is very slow to drop all the tables and migrate again.
It is also still very slow to run the `truncate` query against all the tables.
The idea of this package is to listen for query logs when the tests start and truncated only the table that are involved with the tests and ignore the rest.

Note that no table gets dropped or migrated. It only runs `truncate table_name` query

You can install:

```bash
composer require imanghafoori/laravel-fast-refresh-database --dev
```


### Usage
Add the trait to your test class and call the setup method in `setUp()` so the package can start logging insert queries and truncate only the touched tables after each test:

```php
use Imanghafoori\DatabaseFresh\FastRefreshDatabase;

class MyTest extends TestCase
{
    use FastRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Start watching inserts for this test process
        $this->setupDatabaseAndStartWatchingTables();
    }

    public function test_user_can_run()
    {
        // ... your test code
    }
}

```

Tip: Put the trait and the `setUp()` call into your base `Tests\\TestCase` to enable it for all tests.

Note: An automatic hook may be added in a future update so you won't need to call the setup method manually.

You may also check my other package as well:

- github.com/imanghafoori1
