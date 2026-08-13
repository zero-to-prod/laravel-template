<?php

use App\Helpers\SortDirection;
use App\View\ViewDirectory;

test('a direction is the opposite of its opposite', function (): void {
    foreach (SortDirection::cases() as $SortDirection) {
        expect($SortDirection->opposite()->opposite())->toBe($SortDirection)
            ->and($SortDirection->opposite())->not->toBe($SortDirection);
    }
});

test('every direction names an icon that exists and an aria value', function (): void {
    foreach (SortDirection::cases() as $SortDirection) {
        expect(ViewDirectory::svg->has($SortDirection->icon()))->toBeTrue()
            ->and($SortDirection->aria())->toBeIn(['ascending', 'descending']);
    }
});
