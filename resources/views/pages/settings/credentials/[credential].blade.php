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

    <x-ability-table :abilityTable="$abilityTable"/>
</x-settings-card>
