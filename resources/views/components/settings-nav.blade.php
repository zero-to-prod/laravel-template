@php
    use App\View\DataModels\SettingsNav;
@endphp
<aside aria-label="Settings" class="fixed bottom-0 left-0 top-16 z-10 hidden w-56 border-r border-base-300 bg-base-200 lg:block">
    <ul class="menu w-full gap-1 p-2">
        @foreach(SettingsNav::items() as $NavItem)
            <li>
                <a href="{{$NavItem->url()}}" @class(['menu-active' => $NavItem->active()])>
                    <x-svg :svg="$NavItem->svg()"/>
                    {{$NavItem->label}}
                </a>
            </li>
        @endforeach
    </ul>
</aside>
