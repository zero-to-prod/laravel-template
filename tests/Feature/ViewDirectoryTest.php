<?php

use App\Helpers\SvgName;
use App\View\ViewDirectory;

test('a name is qualified by the case', function (): void {
    expect(ViewDirectory::svg->qualify(SvgName::logo))->toBe('svg.logo')
        ->and(ViewDirectory::svg->has(SvgName::logo))->toBeTrue();
});

test('every svg enum case names an existing view', function (): void {
    foreach (SvgName::cases() as $SvgName) {
        expect(ViewDirectory::svg->has($SvgName))->toBeTrue();
    }
});

test('every svg view is represented by an enum case', function (): void {
    $paths = glob(resource_path('views/svg/*.blade.php')) ?: [];

    expect($paths)->not->toBeEmpty();

    foreach ($paths as $path) {
        expect(SvgName::from(basename($path, '.blade.php')))->toBeInstanceOf(SvgName::class);
    }
});
