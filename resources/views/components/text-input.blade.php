@props(['textInput'])
@php
    use App\View\DataModels\TextInput;
    $TextInput = TextInput::from($textInput);
@endphp
<x-field :fieldset="$TextInput->fieldset()">
    @isset($note)
        <x-slot:note>{{ $note }}</x-slot>
    @elseif($TextInput->configured)
        <x-slot:note><span class="font-normal opacity-60">A {{ $TextInput->configuredLabel }} is configured</span></x-slot>
    @endisset
    @if($TextInput->icon)
        <label class="input w-full @error($TextInput->error, $TextInput->bag) input-error @enderror">
            <x-svg :svg="$TextInput->svg()"/>
            <input name="{{ $TextInput->name }}" value="{{ $TextInput->value }}" type="{{ $TextInput->type }}"
                   @if($TextInput->placeholder) placeholder="{{ $TextInput->placeholder }}" @endif
                   @if($TextInput->autocomplete) autocomplete="{{ $TextInput->autocomplete }}" @endif
                   @if($TextInput->title) title="{{ $TextInput->title }}" @endif
                   @if($TextInput->required) required @endif
                   @if($TextInput->readonly) readonly @endif
                   class="grow"/>
        </label>
    @else
        <input name="{{ $TextInput->name }}" value="{{ $TextInput->value }}" type="{{ $TextInput->type }}"
               @if($TextInput->placeholder) placeholder="{{ $TextInput->placeholder }}" @endif
               @if($TextInput->autocomplete) autocomplete="{{ $TextInput->autocomplete }}" @endif
               @if($TextInput->title) title="{{ $TextInput->title }}" @endif
               @if($TextInput->required) required @endif
               @if($TextInput->readonly) readonly @endif
               class="input w-full @error($TextInput->error, $TextInput->bag) input-error @enderror"/>
    @endif
    {{ $slot }}
</x-field>
