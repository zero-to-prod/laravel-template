@props(['svg'])
@php
    use App\View\DataModels\Svg;
    use App\View\ViewDirectory;
    $Svg = Svg::from($svg);
@endphp
@include(ViewDirectory::svg->qualify($Svg->name), ['classname' => $Svg->classname])
