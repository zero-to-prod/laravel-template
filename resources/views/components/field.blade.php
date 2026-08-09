@props(['fieldset'])
@php
    $Fieldset = App\View\DataModels\Fieldset::from($fieldset);
@endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend" @if($Fieldset->title) title="{{ $Fieldset->title }}"@endif>
        {{ $Fieldset->legend }}@if($Fieldset->required)
            <span class="text-error">*</span>
        @endif
        {{ $note ?? '' }}
    </legend>
    {{ $slot }}
    @if($Fieldset->name)
        @error($Fieldset->name, $Fieldset->bag)<p class="label text-error">{{ $message }}</p>@enderror
    @endif
</fieldset>
