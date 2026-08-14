<?php

use App\Models\User;
use App\Modules\Settings\Authentication\PasswordForm;
use App\Routes\Auth;
use App\View\DataModels\SettingsCard;
use Illuminate\Support\Str;
use Laravel\Head\Facades\Head;

$User = auth()->user();
$OauthProviders = $User instanceof User ? $User->oauthProviders : collect();

Head::title('Security')
    ->description('Review your sign-in methods and update your password.')
    ->hiddenFromRobots();
?>
<x-settings-card :settingsCard="[SettingsCard::title => 'Security']">
    <x-status-toast/>

    <section aria-labelledby="sign-in-methods-heading">
        <div>
            <h2 id="sign-in-methods-heading" class="text-lg font-semibold">Sign in methods</h2>
            <p class="mt-1 text-sm text-base-content/70">Accounts you can use to sign in.</p>
        </div>

        <div class="mt-4 overflow-hidden rounded-box border border-base-300">
            @forelse($OauthProviders as $OauthProvider)
                <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="avatar">
                            <div class="w-11 rounded-full">
                                <img src="{{$OauthProvider->picture}}" alt="" referrerpolicy="no-referrer">
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{Str::headline($OauthProvider->provider_id->value)}}</span>
                                @if($OauthProvider->verified_email)
                                    <span class="badge badge-success badge-sm">Verified</span>
                                @endif
                            </div>
                            <p class="truncate text-sm text-base-content/70">{{$OauthProvider->name}}</p>
                        </div>
                    </div>

                    <dl class="grid gap-1 text-sm sm:text-right">
                        <div>
                            <dt class="sr-only">Email</dt>
                            <dd>{{$OauthProvider->email}}</dd>
                        </div>
                        @if($OauthProvider->hd !== null)
                            <div>
                                <dt class="sr-only">Hosted domain</dt>
                                <dd class="text-base-content/60">{{$OauthProvider->hd}}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @empty
                <p class="p-4 text-sm text-base-content/70">No connected providers.</p>
            @endforelse
        </div>
    </section>

    <div class="divider"></div>

    <section aria-labelledby="password-heading">
        <h2 id="password-heading" class="text-lg font-semibold">Password</h2>
        <p class="text-sm text-base-content/70">
            Confirm your current password to choose a new one.
        </p>
        <form class="mt-2 space-y-4" method="POST" action="{{Auth::settingsSecurity->value}}">
            @csrf
            <input type="text" name="username" autocomplete="username" value="{{auth()->user()?->email}}" readonly hidden>
            <x-text-input :textInput="PasswordForm::textInput(PasswordForm::current_password)"/>
            <x-text-input :textInput="PasswordForm::textInput(PasswordForm::password)"/>
            <x-text-input :textInput="PasswordForm::textInput(PasswordForm::password_confirmation)"/>
            <button class="btn btn-primary">Update Password</button>
        </form>
    </section>
</x-settings-card>
