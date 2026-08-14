<?php

use App\Modules\Settings\Credentials\TokenQuery;
use App\View\DataModels\CredentialsTable;
use App\View\DataModels\SettingsCard;
use Laravel\Head\Facades\Head;

Head::title('Credentials')
    ->description('The personal access tokens that reach this account through the API.')
    ->hiddenFromRobots();
?>
@php
    $tokens = TokenQuery::get(request());
@endphp
<x-settings-card :settingsCard="[SettingsCard::title => 'Credentials']">
    <x-status-toast/>
    <x-credentials-table :credentialsTable="[CredentialsTable::tokens => $tokens]"/>
</x-settings-card>
