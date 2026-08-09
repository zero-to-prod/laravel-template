@props(['svg'])
@php
    $Svg = App\View\DataModels\Svg::from($svg);
@endphp
@include('svg.'.$Svg->name, ['classname' => $Svg->classname])
