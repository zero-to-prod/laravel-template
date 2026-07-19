@props([
    'legend' => null,
    'name',
    'value' => null,
    'type' => null,
    'icon' => null,
    'error' => null,
    'bag' => null,
    'model' => null,
    'placeholder' => null,
    'autocomplete' => null,
    'configured' => false,
    'configuredLabel' => 'value',
])
@php
    use App\Helpers\FieldViewDefaults;
    $error ??= $name;
    $bag ??= FieldViewDefaults::bag($model);
    $type ??= $model ? $model::type($name) : 'text';
    $placeholder ??= FieldViewDefaults::placeholder($model, $name);
    $legend ??= FieldViewDefaults::legend($model, $name);
    $autocomplete ??= $model && $model::isSensitive($name) ? 'new-password' : null;
    $value ??= FieldViewDefaults::value($model, $name);
    $title = FieldViewDefaults::description($model, $name);
@endphp
<x-field :legend="$legend" :name="$error" :bag="$bag" :model="$model" :required="$attributes->get('required')">
    @isset($note)
        <x-slot:note>{{ $note }}</x-slot>
    @elseif($configured)
        <x-slot:note><span class="font-normal opacity-60">A {{ $configuredLabel }} is configured</span></x-slot>
    @endisset
    @if($icon)
        <label class="input w-full @error($error, $bag) input-error @enderror">
            <x-svg :name="$icon" classname="h-4 w-4 opacity-70"/>
            <input name="{{ $name }}" value="{{ $value }}" type="{{ $type }}"
                   @if($placeholder) placeholder="{{ $placeholder }}" @endif
                   @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                   @if($title) title="{{ $title }}" @endif
                   class="grow" {{ $attributes }}/>
        </label>
    @else
        <input name="{{ $name }}" value="{{ $value }}" type="{{ $type }}"
               @if($placeholder) placeholder="{{ $placeholder }}" @endif
               @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
               @if($title) title="{{ $title }}" @endif
               class="input w-full @error($error, $bag) input-error @enderror" {{ $attributes }}/>
    @endif
    {{ $slot }}
</x-field>
