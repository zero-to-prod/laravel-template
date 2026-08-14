<?php

use App\Models\OauthProvider;
use App\Models\User;
use App\Sources\Db\App\OauthProviders;

test('oauth provider belongs to a user', function (): void {
    $User = User::factory()->createOne();
    $OauthProvider = $User->oauthProviders()->create([
        OauthProviders::sub->value => '115454882825190401401',
        OauthProviders::name->value => 'Digital Forte',
        OauthProviders::given_name->value => 'Digital',
        OauthProviders::family_name->value => 'Forte',
        OauthProviders::picture->value => 'https://example.com/avatar.jpg',
        OauthProviders::email->value => 'admin@digitalforte.us',
        OauthProviders::email_verified->value => true,
        OauthProviders::hd->value => 'digitalforte.us',
        OauthProviders::id->value => '115454882825190401401',
        OauthProviders::verified_email->value => true,
        OauthProviders::link->value => null,
    ]);

    expect($OauthProvider)->toBeInstanceOf(OauthProvider::class)
        ->and($OauthProvider->getKey())->toBe('115454882825190401401')
        ->and($OauthProvider->incrementing)->toBeFalse()
        ->and($OauthProvider->timestamps)->toBeFalse()
        ->and($OauthProvider->email_verified)->toBeTrue()
        ->and($OauthProvider->verified_email)->toBeTrue()
        ->and($OauthProvider->user->is($User))->toBeTrue()
        ->and($User->oauthProviders()->sole()->is($OauthProvider))->toBeTrue();
});
