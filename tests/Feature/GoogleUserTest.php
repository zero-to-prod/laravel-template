<?php

use App\Modules\Login\GoogleUser;

test('google user maps the socialite payload', function (): void {
    $GoogleUser = GoogleUser::from([
        GoogleUser::sub => '115454882825190401401',
        GoogleUser::name => 'Digital Forte',
        GoogleUser::given_name => 'Digital',
        GoogleUser::family_name => 'Forte',
        GoogleUser::picture => 'https://example.com/avatar.jpg',
        GoogleUser::email => 'admin@digitalforte.us',
        GoogleUser::email_verified => true,
        GoogleUser::hd => 'digitalforte.us',
        GoogleUser::id => '115454882825190401401',
        GoogleUser::verified_email => true,
        GoogleUser::link => null,
    ]);

    expect($GoogleUser->sub)->toBe('115454882825190401401')
        ->and($GoogleUser->name)->toBe('Digital Forte')
        ->and($GoogleUser->given_name)->toBe('Digital')
        ->and($GoogleUser->family_name)->toBe('Forte')
        ->and($GoogleUser->picture)->toBe('https://example.com/avatar.jpg')
        ->and($GoogleUser->email)->toBe('admin@digitalforte.us')
        ->and($GoogleUser->email_verified)->toBeTrue()
        ->and($GoogleUser->hd)->toBe('digitalforte.us')
        ->and($GoogleUser->id)->toBe('115454882825190401401')
        ->and($GoogleUser->verified_email)->toBeTrue()
        ->and($GoogleUser->link)->toBeNull();
});

test('google user accepts an absent hosted domain and link', function (): void {
    $GoogleUser = GoogleUser::from([
        GoogleUser::sub => '123',
        GoogleUser::name => 'Google User',
        GoogleUser::given_name => 'Google',
        GoogleUser::family_name => 'User',
        GoogleUser::picture => 'https://example.com/avatar.jpg',
        GoogleUser::email => 'google@example.com',
        GoogleUser::email_verified => true,
        GoogleUser::id => '123',
        GoogleUser::verified_email => true,
    ]);

    expect($GoogleUser->hd)->toBeNull()
        ->and($GoogleUser->link)->toBeNull();
});
