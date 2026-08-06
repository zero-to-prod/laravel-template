<?php

use App\Routes\Web;

test('home ok', function (): void {
    $this->get(Web::home->value)->assertOk();
});
