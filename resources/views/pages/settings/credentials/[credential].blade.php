<?php

use App\Modules\Settings\Credentials\TokenQuery;
use App\Routes\Auth;
use App\View\DataModels\AbilityTable;
use App\View\DataModels\SettingsCard;
use App\View\DataModels\Svg;
use Laravel\Head\Facades\Head;

Head::title('Credential')
    ->description('The endpoints one personal access token is allowed to reach.')
    ->hiddenFromRobots();
?>
@php
    $Token = TokenQuery::find(request(), $credential);
    $abilityTable = [
        AbilityTable::id => $Token->id,
        AbilityTable::name => $Token->name,
        AbilityTable::granted => $Token->abilities ?? [],
    ];
@endphp
<x-settings-card :settingsCard="[SettingsCard::title => $Token->name]">
    <x-status-toast/>
    <a href="{{ Auth::settingsCredentials->value }}"
       class="link link-hover inline-flex items-center gap-1 text-sm">
        <x-svg :svg="[Svg::name => 'chevron-up', Svg::classname => 'h-3 w-3 -rotate-90 opacity-70']"/>
        Credentials
    </a>
    <p class="text-sm text-base-content/70">
        The endpoints this token is allowed to reach. A method that is not ticked is refused before it
        reaches the endpoint, and an endpoint with no method ticked is closed to this token entirely.
    </p>

    <x-ability-table :abilityTable="$abilityTable"/>
</x-settings-card>
