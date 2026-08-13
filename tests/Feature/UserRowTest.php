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

    expect($cells)->toHaveSameSize(UsersTable::columns())
        ->and($cells[0])->toBe($User->name)
        ->and($cells[1])->toBe($User->email);
});
