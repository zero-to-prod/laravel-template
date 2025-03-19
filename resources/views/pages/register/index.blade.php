@php
    use App\Modules\Register\RegisterForm;
@endphp

<x-main>
    <div class="card card-compact sm:m-auto sm:mt-24 sm:max-w-sm">
        <div class="card-body">
            <h1 class="card-title">Register</h1>
            <form class="space-y-4" method="POST" action="{{r()->register()}}" class="flex flex-col">
                @csrf
                <label class="w-full form-control">
                    <div class="label">
                        <span class="label-text">Full Name</span>
                    </div>
                    <label class="flex items-center gap-2 input input-bordered bg-base-200">
                        <x-svg name="user" classname="h-4 w-4 opacity-70"/>
                        <input name="{{RegisterForm::name}}" value="{{old(RegisterForm::name)}}" type="text" class="grow"
                               placeholder="First and Last Name" required/>
                    </label>
                </label>
                <label class="w-full form-control">
                    <div class="label">
                        <span class="label-text">Email</span>
                    </div>
                    <label class="flex items-center gap-2 input input-bordered bg-base-200">
                        <x-svg name="email" classname="h-4 w-4 opacity-70"/>
                        <input name="{{RegisterForm::email}}" value="{{old(RegisterForm::email)}}" type="email" class="grow" placeholder="Email"/>
                    </label>
                </label>
                <label class="w-full form-control">
                    <div class="label">
                        <span class="label-text">Password</span>
                    </div>
                    <label class="flex items-center gap-2 input input-bordered bg-base-200">
                        <x-svg name="key" classname="h-4 w-4 opacity-70"/>
                        <input name="{{RegisterForm::password}}" type="password" class="grow" placeholder="Password"/>
                    </label>
                </label>
                <label class="w-full form-control">
                    <div class="label">
                        <span class="label-text">Password Confirmation</span>
                    </div>
                    <label class="flex items-center gap-2 input input-bordered bg-base-200">
                        <x-svg name="key" classname="h-4 w-4 opacity-70"/>
                        <input name="{{RegisterForm::password_confirmation}}" type="password" class="grow" placeholder="Password Confirmation"/>
                    </label>
                </label>
                <div>
                    <button class="btn btn-primary mt-4 w-full">Register</button>
                </div>
                @if(isset($errors))
                    <x-errors classname="shadow" :$errors :take="1"/>
                @endif
            </form>
            <div class="divider">or</div>
            @guest
                <a href="{{r()->login()}}" class="link link-primary text-center p-3">Login</a>
            @endguest
        </div>
    </div>
</x-main>
