<?php

use App\Modules\Settings\Credentials\TokenQuery;
use App\View\DataModels\CredentialsTable;
use App\View\DataModels\SettingsCard;
use Laravel\Head\Facades\Head;

Head::title('Credentials')
    ->description('The personal access tokens that reach this account through the API.')
    ->hiddenFromRobots();
?>
<x-settings-card :settingsCard="[SettingsCard::title => 'Credentials']">
    <x-status-toast/>
    <p class="text-sm text-base-content/70">
        The personal access tokens that reach this account through the API. A token is shown once, when it is
        created, and reaches every endpoint until you narrow it under Manage.
    </p>

    <x-credentials-table :credentialsTable="[CredentialsTable::tokens => TokenQuery::get(request())]"/>
</x-settings-card>
