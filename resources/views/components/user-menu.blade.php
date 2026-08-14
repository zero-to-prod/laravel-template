@props(['userMenu' => []])
@php
    use App\View\DataModels\UserMenu;
    $UserMenu = UserMenu::from($userMenu);
@endphp
<div class="dropdown dropdown-end">
    <div tabindex="0" role="button"
         @class(['btn btn-ghost btn-circle avatar', 'avatar-placeholder' => $UserMenu->picture() === null]) title="{{$UserMenu->name}}">
        @if($UserMenu->picture() !== null)
            <div class="w-9 rounded-full text-neutral-content">
                <img src="{{$UserMenu->picture()}}" alt="{{$UserMenu->name}}" referrerpolicy="no-referrer">
            </div>
        @else
            <div class="w-9 rounded-full bg-neutral text-neutral-content">
                <span class="text-sm">{{$UserMenu->initials()}}</span>
            </div>
        @endif
    </div>
    <ul tabindex="0" class="mt-3 w-64 p-2 shadow menu menu-sm dropdown-content bg-base-300 rounded-box z-1">
        <li class="menu-title">
            <div class="flex items-center gap-3">
                <div @class(['avatar', 'avatar-placeholder' => $UserMenu->picture() === null])>
                    <div class="w-9 rounded-full bg-neutral text-neutral-content">
                        @if($UserMenu->picture() !== null)
                            <img src="{{$UserMenu->picture()}}" alt="{{$UserMenu->name}}" referrerpolicy="no-referrer">
                        @else
                            <span class="text-sm">{{$UserMenu->initials()}}</span>
                        @endif
                    </div>
                </div>
                <div class="min-w-0">
                    <p class="truncate font-semibold text-base-content">{{$UserMenu->name}}</p>
                    <p class="truncate text-xs font-normal opacity-60">{{$UserMenu->email}}</p>
                </div>
            </div>
        </li>
        @foreach(UserMenu::items() as $NavItem)
            <li>
                <a href="{{$NavItem->url()}}">
                    <x-svg :svg="$NavItem->svg()"/>
                    {{$NavItem->label}}
                </a>
            </li>
        @endforeach
    </ul>
</div>
