<?php

namespace Tests\Fixtures;

use App\Helpers\RunsQuery;

class QueryStub
{
    use RunsQuery;

    public function handle(mixed $value): int
    {
        return (is_numeric($value) ? (int) $value : 0) * 2;
    }
}
