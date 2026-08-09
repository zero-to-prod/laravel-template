@props(['authCard' => []])
@php
    $AuthCard = App\View\DataModels\AuthCard::from($authCard);
@endphp
<x-main>
    <div class="{{ $AuthCard->classes() }}">
        <div class="card-body">
            <x-page-header :pageHeader="$AuthCard->pageHeader()">
                @isset($controls)
                    <x-slot:controls>{{ $controls }}</x-slot:controls>
                @endisset
                {{ $slot }}
                @isset($footer)
                    <div class="divider">or</div>
                    {{ $footer }}
                @endisset
            </x-page-header>
        </div>
    </div>
</x-main>
