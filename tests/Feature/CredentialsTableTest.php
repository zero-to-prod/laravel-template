<?php

use App\Models\User;
use App\Modules\Settings\Credentials\TokenForm;
use App\Routes\Auth;
use App\Sources\Db\App\PersonalAccessTokens;
use App\View\DataModels\CredentialRow;
use App\View\DataModels\CredentialsTable;
use App\View\DataModels\TextInput;
use App\View\ViewDirectory;
use Zerotoprod\DataModel\PropertyRequiredException;

/** @param  array<string, mixed>  $overrides */
function credentialsTable(array $overrides = []): CredentialsTable
{
    return CredentialsTable::from([
        CredentialsTable::tokens => [],
        ...$overrides,
    ]);
}

test('the listing is required', function (): void {
    CredentialsTable::from();
})->throws(PropertyRequiredException::class);

test('every column it lists is a real column of the table', function (): void {
    foreach (CredentialsTable::columns() as $Column) {
        expect(PersonalAccessTokens::tryFrom($Column->value))->toBe($Column);
    }
});

test('every column it lists is readable off a row', function (): void {
    $properties = array_keys(get_class_vars(CredentialRow::class));

    foreach (CredentialsTable::columns() as $Column) {
        expect($properties)->toContain($Column->value);
    }
});

test('the secret is never one of the columns', function (): void {
    expect(CredentialsTable::columns())->not->toContain(PersonalAccessTokens::token);
});

test('a heading carries the column comment as its title', function (): void {
    $headers = credentialsTable()->headers();

    expect($headers)->toHaveSameSize(CredentialsTable::columns())
        ->and($headers['Name'])->toBe(PersonalAccessTokens::name->comment());
});

test('a timestamp is headed without the suffix its column name carries', function (): void {
    $headers = credentialsTable()->headers();

    expect(array_keys($headers))->toContain('Last Used', 'Expires', 'Created')
        ->and($headers['Expires'])->toBe(PersonalAccessTokens::expires_at->comment());
});

test('the empty row spans the headings and the revoke column', function (): void {
    expect(credentialsTable()->span())->toBe(count(CredentialsTable::columns()) + 1);
});

test('the form posts to the page it is rendered on', function (): void {
    expect(credentialsTable()->action())->toBe(Auth::settingsCredentials->value);
});

test('the inputs are the ones the form declares', function (): void {
    expect(credentialsTable()->nameInput()[TextInput::name])->toBe(TokenForm::name)
        ->and(credentialsTable()->expiresAtInput()[TextInput::name])->toBe(TokenForm::expires_at);
});

test('the expiry defaults to a month out', function (): void {
    expect(credentialsTable()->expiresAtInput()[TextInput::value])
        ->toBe(now()->addDays(CredentialsTable::expiryDays)->toDateString());
});

test('a submitted expiry outlives the default', function (): void {
    session()->put('_old_input', [TokenForm::expires_at => '2030-01-01']);

    expect(credentialsTable()->expiresAtInput()[TextInput::value])->toBe('2030-01-01');
});

test('an icon an input asks for exists', function (): void {
    expect(ViewDirectory::svg->has((string) TextInput::from(credentialsTable()->nameInput())->icon))->toBeTrue();
});

test('no secret is shown when none was flashed', function (): void {
    expect(credentialsTable()->issued)->toBeNull();
});

test('a flashed secret is shown once', function (): void {
    session()->put(CredentialsTable::sessionKey, 'plain-text-token');

    expect(credentialsTable()->issued)->toBe('plain-text-token');
});

test('a row is built for every token it was given, in the order it was given them', function (): void {
    $User = User::factory()->createOne();
    $tokens = [
        issuedToken($User, $User->createToken('newer'))->toArray(),
        issuedToken($User, $User->createToken('older'))->toArray(),
    ];

    $rows = credentialsTable([CredentialsTable::tokens => $tokens])->rows();

    expect($rows)->toHaveCount(2)
        ->and($rows[0]->name)->toBe('newer')
        ->and($rows[1]->name)->toBe('older');
});
