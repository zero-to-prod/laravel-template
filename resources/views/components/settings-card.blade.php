@props(['settingsCard' => []])
@php
    use App\View\DataModels\SettingsCard;
    $SettingsCard = SettingsCard::from($settingsCard);
@endphp
<x-main>
    <div class="mx-auto max-w-4xl p-4 sm:p-6">
        <h1 class="text-2xl font-semibold">Settings</h1>
        <div class="mt-6 flex flex-col gap-6 sm:flex-row">
            <ul class="menu w-full p-2 rounded-box bg-base-200 sm:w-56 sm:shrink-0">
                @foreach(SettingsCard::sections() as $NavItem)
                    <li>
                        <a href="{{ $NavItem->url() }}" @class(['menu-active' => $NavItem->active()])>
                            <x-svg :svg="$NavItem->svg()"/>
                            {{ $NavItem->label }}
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="min-w-0 flex-1 card border border-base-300 bg-base-100 shadow-sm">
                <div class="card-body">
                    <x-page-header :pageHeader="$SettingsCard->pageHeader()">
                        {{ $slot }}
                    </x-page-header>
                </div>
            </div>
        </div>
    </div>
</x-main>
