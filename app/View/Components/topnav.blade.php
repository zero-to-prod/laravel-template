<div class="fixed top-0 z-10 shadow-md navbar bg-base-100">
    <div class="navbar-start">
        <div class="navbar-start">
            <div class="dropdown">
                <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
                    </svg>
                </div>
                <ul tabindex="0" class="mt-3 w-52 p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box z-[1]">
                    <li><a>Item</a></li>
                    <li>
                        <a>Parent</a>
                        <ul class="p-2">
                            <li><a>Submenu</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <a href="{{r()->home()}}"
               class="hidden text-xl btn btn-ghost no-animation lg:inline-flex"
               title="Go Home"
            >
                {{config('app.name')}}
            </a>
        </div>
    </div>
    <div class="gap-2 navbar-center">
    </div>
    <div class="navbar-end">
        @auth
            <a href="{{r()->logout()}}" class="text-lg btn btn-ghost no-animation">
                Logout
            </a>
        @else
            <a href="{{r()->login()}}" class="text-lg btn btn-ghost no-animation">
                Login
            </a>
        @endauth
    </div>
</div>