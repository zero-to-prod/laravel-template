@php
    use App\View\DataModels\LeftNav;
    use App\View\DataModels\Svg;
@endphp
<aside class="fixed bottom-0 left-0 top-16 z-10 hidden w-56 border-r border-base-300 bg-base-200 lg:block">
    <ul class="menu w-full gap-1 p-2">
        @foreach(LeftNav::items() as $item)
            <li>
                <a href="{{$item['route']->value}}"
                   @class(['menu-active' => $item['route']->isExact(request())])
                >
                    <x-svg :svg="[Svg::name => $item['icon'], Svg::classname => 'h-4 w-4 opacity-70']"/>
                    {{$item['label']}}
                </a>
            </li>
        @endforeach
    </ul>
</aside>