<?php

use App\View\ViewDirectory;

test('a name is qualified by the case', function (): void {
    expect(ViewDirectory::svg->qualify('logo'))->toBe('svg.logo')
        ->and(ViewDirectory::svg->has('logo'))->toBeTrue()
        ->and(ViewDirectory::svg->has('no-such-icon'))->toBeFalse();
});

test('every view in every case directory is reachable through it', function (): void {
    foreach (ViewDirectory::cases() as $case) {
        $paths = glob(resource_path('views/'.$case->value.'/*.blade.php')) ?: [];

        expect($paths)->not->toBeEmpty();

        foreach ($paths as $path) {
            expect($case->has(basename($path, '.blade.php')))->toBeTrue();
        }
    }
});
