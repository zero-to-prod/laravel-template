<?php

use App\Models\User;
use App\Routes\Auth;
use App\Sources\Db\App\PersonalAccessTokens;
use App\View\DataModels\CredentialRow;
use App\View\DataModels\CredentialsTable;
use Illuminate\Support\Carbon;
use Zerotoprod\DataModel\PropertyRequiredException;

/** @return array<string, mixed> */
function credentialToken(User $User, ?string $expiresAt = null): array
{
    return issuedToken(
        $User,
        $User->createToken('Laptop CLI', expiresAt: $expiresAt === null ? null : Carbon::parse($expiresAt)),
    )->toArray();
}

test('a row hydrates from the token it renders', function (): void {
    $User = User::factory()->createOne();
    $CredentialRow = CredentialRow::from(credentialToken($User));

    expect($CredentialRow->name)->toBe('Laptop CLI')
        ->and($CredentialRow->abilities)->toBe(['*']);
});

test('the id and the name are required', function (): void {
    CredentialRow::from([CredentialRow::name => 'Laptop CLI']);
})->throws(PropertyRequiredException::class);

test('a row never carries the secret', function (): void {
    $CredentialRow = CredentialRow::from(credentialToken(User::factory()->createOne()));

    expect(array_keys(get_object_vars($CredentialRow)))
        ->not->toContain(PersonalAccessTokens::token->value);
});

test('a timestamp renders as a date and an absent one as a dash', function (): void {
    $CredentialRow = CredentialRow::from(credentialToken(User::factory()->createOne()));

    expect($CredentialRow->cell(PersonalAccessTokens::last_used_at))->toBe('—')
        ->and($CredentialRow->cell(PersonalAccessTokens::expires_at))->toBe('—')
        ->and($CredentialRow->cell(PersonalAccessTokens::created_at))->toBe(now()->toFormattedDateString());
});

test('abilities render as a list', function (): void {
    $CredentialRow = CredentialRow::from(credentialToken(User::factory()->createOne()));

    expect($CredentialRow->cell(PersonalAccessTokens::abilities))->toBe('*');
});

test('a token granted nothing renders a dash rather than an empty cell', function (): void {
    $granted = CredentialRow::from([
        ...credentialToken(User::factory()->createOne()),
        CredentialRow::abilities => [],
    ]);

    $ungranted = CredentialRow::from([
        ...credentialToken(User::factory()->createOne()),
        CredentialRow::abilities => null,
    ]);

    expect($granted->cell(PersonalAccessTokens::abilities))->toBe('—')
        ->and($ungranted->cell(PersonalAccessTokens::abilities))->toBe('—');
});

test('the cells line up with the headings, in order', function (): void {
    $cells = CredentialRow::from(credentialToken(User::factory()->createOne()))->cells();

    expect($cells)->toHaveSameSize(CredentialsTable::columns())
        ->and($cells[0])->toBe('Laptop CLI');
});

test('a token expiring in the future is not expired', function (): void {
    $CredentialRow = CredentialRow::from(credentialToken(User::factory()->createOne(), now()->addDay()->toDateTimeString()));

    expect($CredentialRow->expired())->toBeFalse();
});

test('a token whose expiry has passed is expired', function (): void {
    $CredentialRow = CredentialRow::from(credentialToken(User::factory()->createOne(), now()->subDay()->toDateTimeString()));

    expect($CredentialRow->expired())->toBeTrue();
});

test('a token with no expiry is never expired', function (): void {
    expect(CredentialRow::from(credentialToken(User::factory()->createOne()))->expired())->toBeFalse();
});

test('the revoke url carries the token id', function (): void {
    $CredentialRow = CredentialRow::from(credentialToken(User::factory()->createOne()));

    expect($CredentialRow->revokeUrl())
        ->toBe(Auth::settingsCredential->url([Auth::credentialParameter => $CredentialRow->id]));
});
