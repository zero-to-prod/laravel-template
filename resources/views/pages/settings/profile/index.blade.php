<?php

use App\Modules\Settings\Profile\ProfileForm;
use App\Routes\Web;
use App\View\DataModels\SettingsCard;
use App\View\DataModels\TextInput;

?>
<x-settings-card :settingsCard="[SettingsCard::title => 'Profile']">
    <x-status-toast/>
    <p class="text-sm text-base-content/70">
        The name other people see on your account.
    </p>
    <form class="mt-2 space-y-4" method="POST" action="{{Web::settingsProfile->value}}">
        @csrf
        <x-text-input :textInput="[
            ...ProfileForm::textInput(ProfileForm::name),
            TextInput::value => old(ProfileForm::name, auth()->user()?->name),
        ]"/>
        <button class="btn btn-primary">Save</button>
    </form>
</x-settings-card>
