<?php

use App\View\DataModels\AuthCard;
use App\View\DataModels\PageHeader;

test('defaults are resolved from the props array', function (): void {
    $PageHeader = PageHeader::from([]);

    expect($PageHeader->title)->toBeNull()
        ->and($PageHeader->classname)->toBe('card-title');
});

test('props override defaults', function (): void {
    $PageHeader = PageHeader::from([
        PageHeader::title => 'Register',
        PageHeader::classname => 'text-lg',
    ]);

    expect($PageHeader->title)->toBe('Register')
        ->and($PageHeader->classname)->toBe('text-lg');
});

test('an auth card projects its heading props', function (): void {
    $PageHeader = PageHeader::from(AuthCard::from([AuthCard::title => 'Register'])->pageHeader());

    expect($PageHeader->title)->toBe('Register')
        ->and(PageHeader::from(AuthCard::from([])->pageHeader())->title)->toBeNull();
});
