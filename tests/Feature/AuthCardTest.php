<?php

use App\View\DataModels\AuthCard;

test('defaults are resolved from the props array', function (): void {
    $AuthCard = AuthCard::from();

    expect($AuthCard->title)->toBeNull()
        ->and($AuthCard->maxWidth)->toBe('sm:max-w-sm')
        ->and($AuthCard->classname)->toBeEmpty()
        ->and($AuthCard->classes())->toBe('card sm:m-auto sm:mt-24 sm:max-w-sm');
});

test('props override defaults and compose into the card classes', function (): void {
    $AuthCard = AuthCard::from([
        AuthCard::title => 'Register',
        AuthCard::maxWidth => 'sm:max-w-lg',
        AuthCard::classname => 'card-compact',
    ]);

    expect($AuthCard->title)->toBe('Register')
        ->and($AuthCard->classes())->toBe('card sm:m-auto sm:mt-24 sm:max-w-lg card-compact');
});
