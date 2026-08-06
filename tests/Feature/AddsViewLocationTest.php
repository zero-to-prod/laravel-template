<?php

use App\Exceptions\ViewNotFound;
use App\Helpers\Rule;
use Tests\Fixtures\Views\ViewStub;

test('a null view name resolves to the first view in the namespace directory', function (): void {
    expect(new ViewStub()->localView())->toBe('first');
});

test('a nested view is resolved in dot notation', function (): void {
    expect(new ViewStub()->localView('nested.second'))->toBe('nested.second');
});

test('the view location is registered so the resolved view renders', function (): void {
    expect(view(new ViewStub()->localView('first'))->render())->toContain('first');
});

test('an unknown view name is rejected', function (): void {
    new ViewStub()->localView('missing');
})->throws(ViewNotFound::class, "View: 'missing' not found in");

test('a namespace directory without views is rejected', function (): void {
    new ViewStub()->localView(null, Rule::class);
})->throws(ViewNotFound::class, 'No views found in');

test('a namespace directory that does not exist yields no views', function (): void {
    expect(ViewStub::getViewNames(base_path('does/not/exist')))->toBeEmpty();
});

test('an unknown class name is reported as a runtime exception', function (): void {
    new ViewStub()->localView(null, 'Tests\Fixtures\Views\NotAClass');
})->throws(RuntimeException::class);
