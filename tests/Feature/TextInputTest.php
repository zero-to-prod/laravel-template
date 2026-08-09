<?php

use App\View\DataModels\TextInput;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Zerotoprod\DataModel\PropertyRequiredException;

test('defaults are resolved from the props array', function (): void {
    $TextInput = TextInput::from([TextInput::name => 'email']);

    expect($TextInput->name)->toBe('email')
        ->and($TextInput->error)->toBe('email')
        ->and($TextInput->type)->toBe('text')
        ->and($TextInput->bag)->toBe('default')
        ->and($TextInput->configuredLabel)->toBe('value')
        ->and($TextInput->required)->toBeFalse()
        ->and($TextInput->configured)->toBeFalse()
        ->and($TextInput->legend)->toBeNull()
        ->and($TextInput->icon)->toBeNull()
        ->and($TextInput->title)->toBeNull()
        ->and($TextInput->placeholder)->toBeNull()
        ->and($TextInput->autocomplete)->toBeNull();
});

test('props override defaults', function (): void {
    $TextInput = TextInput::from([
        TextInput::name => 'email',
        TextInput::error => 'custom',
        TextInput::type => 'email',
        TextInput::bag => 'register_form',
        TextInput::value => 'explicit',
        TextInput::required => true,
    ]);

    expect($TextInput->error)->toBe('custom')
        ->and($TextInput->type)->toBe('email')
        ->and($TextInput->bag)->toBe('register_form')
        ->and($TextInput->value)->toBe('explicit')
        ->and($TextInput->required)->toBeTrue();
});

test('a name is required', function (): void {
    TextInput::from([]);
})->throws(PropertyRequiredException::class);

test('value falls back to old input, except for passwords', function (): void {
    $Store = new Store('test', new ArraySessionHandler(1));
    $Store->put('_old_input', ['email' => 'old@example.com', 'password' => 'secret']);
    request()->setLaravelSession($Store);

    expect(TextInput::from([TextInput::name => 'email'])->value)->toBe('old@example.com')
        ->and(TextInput::from([TextInput::name => 'password', TextInput::type => 'password'])->value)->toBeNull();
});
