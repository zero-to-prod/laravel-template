@props(['settingsCard' => []])
@php
    use App\View\DataModels\SettingsCard;
    $SettingsCard = SettingsCard::from($settingsCard);
@endphp
<x-main>
    <div class="mx-auto max-w-5xl p-4 sm:p-6">
        <h1 class="text-2xl font-semibold">Settings</h1>
        <div class="mt-6 card bg-base-100">
            <div class="card-body">
                <x-page-header :pageHeader="$SettingsCard->pageHeader()">
                    {{ $slot }}
                </x-page-header>
            </div>
        </div>
    </div>
</x-main>
