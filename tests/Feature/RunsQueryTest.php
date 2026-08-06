<?php

use Illuminate\Support\Facades\Event;
use Tests\Fixtures\QueryStub;

test('get dispatches the query as an event and returns the handler result', function (): void {
    Event::fake();

    expect(QueryStub::get(2))->toBe(4);

    Event::assertDispatched(QueryStub::class);
});
