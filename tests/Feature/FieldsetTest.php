<?php

use App\View\DataModels\Fieldset;
use App\View\DataModels\TextInput;

test('defaults are resolved from the props array', function (): void {
    $Fieldset = Fieldset::from([]);

    expect($Fieldset->bag)->toBe('default')
        ->and($Fieldset->required)->toBeFalse()
        ->and($Fieldset->legend)->toBeNull()
        ->and($Fieldset->name)->toBeNull()
        ->and($Fieldset->title)->toBeNull();
});

test('props override defaults', function (): void {
    $Fieldset = Fieldset::from([
        Fieldset::legend => 'Email',
        Fieldset::name => 'email',
        Fieldset::bag => 'register_form',
        Fieldset::required => true,
        Fieldset::title => 'User email address',
    ]);

    expect($Fieldset->legend)->toBe('Email')
        ->and($Fieldset->name)->toBe('email')
        ->and($Fieldset->bag)->toBe('register_form')
        ->and($Fieldset->required)->toBeTrue()
        ->and($Fieldset->title)->toBe('User email address');
});

test('a text input projects its fieldset props, reporting errors against the error key', function (): void {
    $Fieldset = Fieldset::from(
        TextInput::from([
            TextInput::name => 'email',
            TextInput::error => 'custom',
            TextInput::legend => 'Email',
            TextInput::required => true,
        ])->fieldset()
    );

    expect($Fieldset->name)->toBe('custom')
        ->and($Fieldset->legend)->toBe('Email')
        ->and($Fieldset->required)->toBeTrue()
        ->and($Fieldset->bag)->toBe('default');
});
