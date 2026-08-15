<?php

use App\Models\User;
use App\Sources\Db\App\Users;
use App\View\DataModels\UserRow;
use App\View\DataModels\UsersTable;
use Zerotoprod\DataModel\PropertyRequiredException;

test('a row hydrates from the model it renders', function (): void {
    $User = User::factory()->createOne();
    $UserRow = UserRow::from($User->toArray());

    expect($UserRow->name)->toBe($User->name)
        ->and($UserRow->email)->toBe($User->email);
});

test('the name and email are required', function (): void {
    UserRow::from([UserRow::name => 'Ada Lovelace']);
})->throws(PropertyRequiredException::class);

test('a timestamp renders as a date and an absent one as a dash', function (): void {
    $UserRow = UserRow::from(User::factory()->unverified()->createOne()->toArray());

    expect($UserRow->cell(Users::email_verified_at))->toBe('—')
        ->and($UserRow->cell(Users::created_at))->toBe(now()->toFormattedDateString());
});

test('the cells line up with the headings, in order', function (): void {
    $User = User::factory()->createOne();
    $cells = UserRow::from($User->toArray())->cells();

    expect($cells)->toHaveCount(count(UsersTable::columns()) + 1)
        ->and($cells[0])->toBe($User->name)
        ->and($cells[1])->toBe($User->email);
});

test('the last session renders as a time and an absent one as a dash', function (): void {
    $User = User::factory()->createOne();

    expect(UserRow::from($User->toArray())->lastSession())->toBe('—')
        ->and(UserRow::from([
            ...$User->toArray(),
            UserRow::last_session_at => now()->timestamp,
        ])->lastSession())->toBe(now()->toDayDateTimeString());
});

test('initials are taken from the first and last word of the name', function (): void {
    $UserRow = UserRow::from(User::factory()->createOne([Users::name->value => 'Ada Byron Lovelace'])->toArray());

    expect($UserRow->initials())->toBe('AL');
});

test('an empty name uses a question mark for initials', function (): void {
    $User = User::factory()->createOne();
    $attributes = $User->toArray();
    $attributes[Users::name->value] = '';

    expect(UserRow::from($attributes)->initials())->toBe('?');
});

test('a row uses gravatar when no provider picture is available', function (): void {
    $User = User::factory()->createOne([Users::email->value => 'MyEmailAddress@example.com']);

    expect(UserRow::from($User->toArray())->picture())
        ->toBe('https://www.gravatar.com/avatar/84059b07d4be67b806386c0aad8070a23f18836bbaae342275dc0a83414c32ee?s=80&d=404&r=g');
});
