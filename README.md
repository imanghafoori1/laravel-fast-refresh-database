For large databases with more than 100 tables it is very slow to drop all the tables and migrate again.
It is also still very slow to run the `truncate` query against all the table.
The idea of this package is to listen for query logs when the tests start and truncated only the table that are involved with the tests and ignore the rest.

Note that no table gets dropped or migrated. it only run `truncate table_name` query

You can install:

```bash
composer require imanghafoori/laravel-fast-refresh-database --dev
```


### Usage:
You should only use the trait in your test class:

```php
class MyTest extends TestCase
{
    use FastRefreshDatabase;

    public function test_user_can_run()
    {
    
    }
} 

```

that it.

You may also check my other package as well:

- github.com/imanghafoori
