<?php

use App\Helpers\SvgName;
use App\View\DataModels\Svg;
use App\View\DataModels\TextInput;
use Zerotoprod\DataModel\PropertyRequiredException;

test('defaults are resolved from the props array', function (): void {
    $Svg = Svg::from([Svg::name => SvgName::email]);

    expect($Svg->name)->toBe(SvgName::email)
        ->and($Svg->classname)->toBeEmpty();
});

test('a name is required', function (): void {
    Svg::from([]);
})->throws(PropertyRequiredException::class);

test('a text input projects its icon props', function (): void {
    $Svg = Svg::from(TextInput::from([TextInput::name => 'email', TextInput::icon => SvgName::email])->svg());

    expect($Svg->name)->toBe(SvgName::email)
        ->and($Svg->classname)->toBe('h-4 w-4 opacity-70');
});
