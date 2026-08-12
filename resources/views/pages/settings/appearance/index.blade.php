<?php

use App\Helpers\Theme;
use App\Modules\Settings\Appearance\AppearanceRequest;
use App\Routes\Web;
use App\View\DataModels\SettingsCard;
use App\View\DataModels\Svg;
use Laravel\Head\Facades\Head;

Head::title('Appearance')
    ->description('Choose how this app looks to you.')
    ->hiddenFromRobots();
?>
@php
    $selected = old(AppearanceRequest::theme, auth()->user()?->theme?->value);
@endphp
<x-settings-card :settingsCard="[SettingsCard::title => 'Appearance']">
    <x-status-toast/>
    <p class="text-sm text-base-content/70">
        Choose how this app looks to you.
    </p>
    <form class="mt-2 space-y-4" method="POST" action="{{Web::settingsAppearance->value}}">
        @csrf
        <fieldset class="fieldset gap-2">
            <legend class="fieldset-legend">Theme</legend>
            @foreach(Theme::cases() as $Theme)
                <label class="flex cursor-pointer items-center gap-3 p-3 border border-base-300 rounded-box hover:bg-base-200">
                    <input type="radio"
                           name="{{AppearanceRequest::theme}}"
                           value="{{$Theme->value}}"
                           class="radio radio-primary"
                           @checked($selected === $Theme->value)
                    />
                    <x-svg :svg="[Svg::name => $Theme->icon(), Svg::classname => 'h-4 w-4 opacity-70']"/>
                    <div class="min-w-0">
                        <p class="font-medium">{{$Theme->label()}}</p>
                        <p class="text-xs opacity-60">{{$Theme->description()}}</p>
                    </div>
                </label>
            @endforeach
            @error(AppearanceRequest::theme)<p class="label text-error">{{$message}}</p>@enderror
        </fieldset>
        <button class="btn btn-primary">Save</button>
    </form>
</x-settings-card>
