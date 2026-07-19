@props(['legend' => null, 'name' => null, 'bag' => null, 'required' => null, 'model' => null])
@php
    use App\Helpers\FieldViewDefaults;
    $bag ??= FieldViewDefaults::bag($model);
    $required ??= $model && $name ? $model::isRequired($name) : false;
    $legend ??= FieldViewDefaults::legend($model, $name);
    $title = FieldViewDefaults::description($model, $name);
@endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend"@if($title) title="{{ $title }}"@endif>
        {{ $legend }}@if($required)<span class="text-error">*</span>@endif
        {{ $note ?? '' }}
    </legend>
    {{ $slot }}
    @if($name)
        @error($name, $bag)<p class="label text-error">{{ $message }}</p>@enderror
    @endif
</fieldset>
