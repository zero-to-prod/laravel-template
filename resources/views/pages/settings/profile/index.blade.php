<?php

use App\Helpers\SvgName;
use App\Models\User;
use App\Modules\Settings\Profile\ProfileForm;
use App\Routes\Auth;
use App\View\DataModels\SettingsCard;
use App\View\DataModels\TextInput;
use Laravel\Head\Facades\Head;

$User = auth()->user();

Head::title('Profile')
    ->description('The name other people see on your account.')
    ->hiddenFromRobots();
?>
<x-settings-card :settingsCard="[SettingsCard::title => 'Profile']">
    <x-status-toast/>
    <form class="mt-2 space-y-4" method="POST" action="{{Auth::settingsProfile->value}}">
        @csrf
        <x-text-input :textInput="[
            ...ProfileForm::textInput(ProfileForm::name),
            TextInput::value => old(ProfileForm::name, auth()->user()?->name),
        ]"/>
        <x-text-input :textInput="[
            TextInput::name => 'email',
            TextInput::legend => 'Email',
            TextInput::type => 'email',
            TextInput::value => $User?->email,
            TextInput::icon => SvgName::email,
            TextInput::autocomplete => 'email',
            TextInput::readonly => true,
            TextInput::title => 'Email cannot be changed',
        ]">
            @if($User instanceof User && $User->hasVerifiedEmail())
                <x-slot:note><span class="badge badge-success badge-sm">Verified</span></x-slot:note>
            @endif
        </x-text-input>
        <button class="btn btn-primary">Save</button>
    </form>
</x-settings-card>
