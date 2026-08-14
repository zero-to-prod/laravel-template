<?php

use App\Helpers\Role;
use App\Helpers\SvgName;
use App\Modules\Admin\Users\Update\UsersUpdateForm;
use App\Modules\Admin\Users\Update\UsersUpdateRequest;
use App\Routes\Admin;
use App\View\DataModels\Svg;
use App\View\DataModels\TextInput;
use Laravel\Head\Facades\Head;

Head::title('User')
    ->description('Manage a registered user.')
    ->hiddenFromRobots();
?>
@php
    $action = Admin::user->url([Admin::userParameter => $user->id]);
    $verified = (bool) old(UsersUpdateRequest::verified, $user->email_verified_at !== null);
    $administrator = (bool) old(UsersUpdateRequest::admin, $user->hasRole(Role::admin->value));
@endphp
<x-main>
    <div class="mx-auto max-w-2xl p-4 sm:p-6">
        <a href="{{Admin::users->value}}" class="link link-hover inline-flex items-center gap-1 text-sm">
            <x-svg :svg="[Svg::name => SvgName::chevron_up, Svg::classname => 'h-3 w-3 -rotate-90 opacity-70']"/>
            Users
        </a>

        <div class="card mt-4 border border-base-300 bg-base-100 shadow-sm">
            <div class="card-body">
                <h1 class="card-title">{{$user->name}}</h1>
                <x-status-toast/>
                <p class="text-sm text-base-content/70">
                    Everything an administrator can change about this account.
                </p>

                <form class="mt-2 space-y-4" method="POST" action="{{$action}}">
                    @csrf
                    <x-text-input :textInput="[
                        ...UsersUpdateForm::textInput(UsersUpdateForm::name),
                        TextInput::value => old(UsersUpdateForm::name, $user->name),
                    ]"/>
                    <x-text-input :textInput="[
                        ...UsersUpdateForm::textInput(UsersUpdateForm::email),
                        TextInput::value => old(UsersUpdateForm::email, $user->email),
                    ]"/>

                    <fieldset class="fieldset gap-2">
                        <legend class="fieldset-legend">Access</legend>
                        <label class="flex cursor-pointer items-center gap-3 rounded-box border border-base-300 p-3 hover:bg-base-200">
                            <input type="checkbox"
                                   name="{{UsersUpdateRequest::verified}}"
                                   value="1"
                                   class="checkbox checkbox-primary"
                                    @checked($verified)
                            />
                            <div class="min-w-0">
                                <p class="font-medium">Email verified</p>
                                <p class="text-xs opacity-60">Clearing this asks the user to confirm their address again.</p>
                            </div>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-box border border-base-300 p-3 hover:bg-base-200">
                            <input type="checkbox"
                                   name="{{UsersUpdateRequest::admin}}"
                                   value="1"
                                   class="checkbox checkbox-primary"
                                    @checked($administrator)
                            />
                            <div class="min-w-0">
                                <p class="font-medium">Administrator</p>
                                <p class="text-xs opacity-60">Grants every page behind the admin rail.</p>
                            </div>
                        </label>
                        @error(UsersUpdateRequest::admin)<p class="label text-error">{{$message}}</p>@enderror
                    </fieldset>

                    <button class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</x-main>
