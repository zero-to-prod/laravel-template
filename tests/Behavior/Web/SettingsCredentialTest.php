<?php

use App\Helpers\HttpVerb;
use App\Models\User;
use App\Modules\Settings\Credentials\TokenUpdateRequest;
use App\Routes\ApiRoute;
use App\Routes\Auth;
use App\Routes\Web;
use App\View\DataModels\AbilityTable;

/** The management page of a token the given account owns. */
function credentialUrl(User $User, string $name = 'Ability Grid'): string
{
    return Auth::settingsCredential->url([
        Auth::credentialParameter => issuedToken($User, $User->createToken($name))->id,
    ]);
}

test('guests are redirected to login', function (): void {
    $url = credentialUrl(User::factory()->createOne());

    $this->get($url)->assertRedirect(Web::login->value);
    $this->post($url)->assertRedirect(Web::login->value);
});

test('the page lists every endpoint a token can be granted, and every verb', function (): void {
    $User = User::factory()->createOne();

    $response = $this->actingAs($User)->get(credentialUrl($User))->assertOk();

    $response->assertSee('Endpoint')
        ->assertSee(ApiRoute::user->value)
        ->assertSee(ApiRoute::user_token->value);

    foreach (HttpVerb::cases() as $HttpVerb) {
        $response->assertSee($HttpVerb->value);
    }
});

test('a toggle is offered only where a verb is bound to the path', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->get(credentialUrl($User))
        ->assertOk()
        ->assertSee(HttpVerb::patch->ability(ApiRoute::user->value))
        ->assertDontSee(HttpVerb::put->ability(ApiRoute::user->value));
});

test('a page reached without a token of your own is not found', function (): void {
    $Owner = User::factory()->createOne();
    $url = credentialUrl($Owner, 'Theirs');

    $this->actingAs(User::factory()->createOne())->get($url)->assertNotFound();
    $this->actingAs(User::factory()->createOne())->post($url)->assertNotFound();
});

test('a token issued from the ui holds every ability, and says so', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->get(credentialUrl($User))
        ->assertOk()
        ->assertSee('This token holds every ability.');
});

test('the verbs ticked are the abilities the token is left holding', function (): void {
    $User = User::factory()->createOne();
    $url = credentialUrl($User);
    $granted = [HttpVerb::get->ability(ApiRoute::user->value), HttpVerb::patch->ability(ApiRoute::user->value)];

    $this->actingAs($User)
        ->from($url)
        ->post($url, [TokenUpdateRequest::abilities => $granted])
        ->assertRedirect($url)
        ->assertSessionHas('status', 'Abilities updated.');

    expect($User->tokens()->sole()->abilities)->toBe($granted);
});

test('a granted verb is ticked when the page is read back', function (): void {
    $User = User::factory()->createOne();
    $url = credentialUrl($User);

    $this->actingAs($User)
        ->post($url, [TokenUpdateRequest::abilities => [HttpVerb::get->ability(ApiRoute::user->value)]]);

    $this->actingAs($User)
        ->get($url)
        ->assertOk()
        ->assertDontSee('This token holds every ability.')
        ->assertSee(AbilityTable::field);
});

test('ticking nothing closes the token to the whole api', function (): void {
    $User = User::factory()->createOne();
    $url = credentialUrl($User);

    $this->actingAs($User)->post($url)->assertSessionHasNoErrors();

    expect($User->tokens()->sole()->abilities)->toBe([]);
});

test('an ability the grid never offered is not stored', function (): void {
    $User = User::factory()->createOne();
    $url = credentialUrl($User);

    $this->actingAs($User)->post($url, [
        TokenUpdateRequest::abilities => [HttpVerb::every, 'DELETE'.HttpVerb::separator.'/api/nowhere'],
    ]);

    expect($User->tokens()->sole()->abilities)->toBe([]);
});
