@props(['title' => null, 'maxWidth' => 'sm:max-w-sm'])
<x-main>
    <div {{ $attributes->merge(['class' => "card sm:m-auto sm:mt-24 $maxWidth"]) }}>
        <div class="card-body">
            <x-page-header>
                <x-slot:title>
                    @if($title)
                        <h1 class="card-title">{{ $title }}</h1>
                    @endif
                </x-slot:title>
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
