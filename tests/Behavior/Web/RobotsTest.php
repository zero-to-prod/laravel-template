<?php

use App\Routes\Web;

test('robots.txt is served as plain text', function (): void {
    $this->get(Web::robots->value)
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=utf-8')
        ->assertSee((string) file_get_contents(resource_path('robots.txt')), false);
});

// A robots.txt without a User-agent line binds no crawler to any of its rules.
test('robots.txt opens with a User-agent group', function (): void {
    expect($this->get(Web::robots->value)->getContent())->toStartWith('User-agent: ');
});
