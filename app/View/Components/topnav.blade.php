@php
use App\Routes\Web;
use App\View\DataModels\LeftNav;
use App\View\DataModels\Svg;
use App\View\DataModels\UserMenu;
@endphp
<div class="fixed top-0 z-20 shadow-md navbar bg-base-100">
    <div class="navbar-start">
        <div class="navbar-start">
            @if($leftNav)
                <div class="dropdown lg:hidden">
                    <div tabindex="0" role="button" class="btn btn-ghost" title="Open navigation">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
                        </svg>
                    </div>
                    <ul tabindex="0" class="mt-3 w-52 p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box z-[1]">
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
                </div>
            @endif
            <a href="{{Web::home->value}}"
               class="btn btn-ghost no-animation hover:border-transparent hover:bg-transparent hover:shadow-none"
               title="Go Home"
            >
                <x-svg :svg="[Svg::name => 'logo', Svg::classname => 'h-6 w-6']"/>
            </a>
            <span class="lg:inline-flex ml-2" title="Company Name">
                {{config('app.name')}}
            </span>
        </div>
    </div>
    <div class="gap-2 navbar-center">
    </div>
    <div class="navbar-end">
        @auth
            <x-user-menu :userMenu="[
                UserMenu::name => auth()->user()?->name ?? '',
                UserMenu::email => auth()->user()?->email ?? '',
            ]"/>
        @else
            <a href="{{Web::login->value}}" class="text-lg btn btn-ghost no-animation">
                Login
            </a>
        @endauth
    </div>
</div>