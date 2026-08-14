<?php

use App\Helpers\Role;
use App\Helpers\SvgName;
use App\Helpers\Theme;
use App\Modules\Admin\Users\Delete\UserDeleteController;
use App\Modules\Admin\Users\Update\UsersUpdateForm;
use App\Modules\Admin\Users\Update\UsersUpdateRequest;
use App\Routes\Admin;
use App\View\DataModels\Svg;
use App\View\DataModels\TextInput;
use Illuminate\Support\Str;
use Laravel\Head\Facades\Head;

Head::title('User')
    ->description('Manage a registered user.')
    ->hiddenFromRobots();
?>
@php
    $user->load('oauthProviders');
    $action = Admin::user->url([Admin::userParameter => $user->id]);
    $verified = (bool) old(UsersUpdateRequest::verified, $user->email_verified_at !== null);
    $administrator = (bool) old(UsersUpdateRequest::admin, $user->hasRole(Role::admin->value));
    $theme = old(UsersUpdateRequest::theme, $user->theme->value);
@endphp
<x-main>
    <div class="mx-auto max-w-5xl p-4 sm:p-6">
        <a href="{{Admin::users->value}}" class="link link-hover inline-flex items-center gap-1 text-sm">
            <x-svg :svg="[Svg::name => SvgName::chevron_up, Svg::classname => 'h-3 w-3 -rotate-90 opacity-70']"/>
            Users
        </a>

        <header class="mt-4 flex flex-col gap-2 border-b border-base-300 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-base-content/55">User account</p>
                <h1 class="mt-1 text-2xl font-semibold">{{$user->name}}</h1>
                <p class="mt-1 font-mono text-xs text-base-content/55">{{$user->id}}</p>
            </div>
            <div class="flex gap-2">
                @if($user->email_verified_at !== null)
                    <span class="badge badge-success">Email verified</span>
                @else
                    <span class="badge badge-warning">Email unverified</span>
                @endif
                @if($administrator)<span class="badge badge-primary">Administrator</span>@endif
            </div>
        </header>

        <x-status-toast/>

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(18rem,0.6fr)]">
            <section class="card border border-base-300 bg-base-100 shadow-sm" aria-labelledby="account-heading">
                <div class="card-body">
                    <h2 id="account-heading" class="card-title">Account information</h2>
                    <p class="text-sm text-base-content/70">Identity, access, appearance, and password controls.</p>

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
                            <legend class="fieldset-legend">Theme</legend>
                            <div class="grid gap-2 sm:grid-cols-3">
                                @foreach(Theme::cases() as $Theme)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-box border border-base-300 p-3 hover:bg-base-200">
                                        <input type="radio" name="{{UsersUpdateRequest::theme}}" value="{{$Theme->value}}"
                                               class="radio radio-primary radio-sm" @checked($theme === $Theme->value)/>
                                        <x-svg :svg="[Svg::name => $Theme->icon(), Svg::classname => 'h-4 w-4 opacity-70']"/>
                                        <span class="text-sm font-medium">{{$Theme->label()}}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error(UsersUpdateRequest::theme)<p class="label text-error">{{$message}}</p>@enderror
                        </fieldset>

                        <fieldset class="fieldset gap-2">
                            <legend class="fieldset-legend">Access</legend>
                            <label class="flex cursor-pointer items-center gap-3 rounded-box border border-base-300 p-3 hover:bg-base-200">
                                <input type="checkbox" name="{{UsersUpdateRequest::verified}}" value="1"
                                       class="checkbox checkbox-primary" @checked($verified)/>
                                <span><span class="block font-medium">Email verified</span><span class="text-xs opacity-60">Clearing this asks the user to confirm their address again.</span></span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-box border border-base-300 p-3 hover:bg-base-200">
                                <input type="checkbox" name="{{UsersUpdateRequest::admin}}" value="1"
                                       class="checkbox checkbox-primary" @checked($administrator)/>
                                <span><span class="block font-medium">Administrator</span><span class="text-xs opacity-60">Grants every page behind the admin rail.</span></span>
                            </label>
                            @error(UsersUpdateRequest::admin)<p class="label text-error">{{$message}}</p>@enderror
                        </fieldset>

                        <div class="divider">Set a new password</div>
                        <p class="text-sm text-base-content/65">Leave both fields blank to keep the current password.</p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-text-input :textInput="UsersUpdateForm::textInput(UsersUpdateForm::password)"/>
                            <x-text-input :textInput="UsersUpdateForm::textInput(UsersUpdateForm::password_confirmation)"/>
                        </div>

                        <button class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </section>

            <aside class="card h-fit border border-base-300 bg-base-100" aria-labelledby="record-heading">
                <div class="card-body">
                    <h2 id="record-heading" class="card-title text-lg">Record details</h2>
                    <dl class="divide-y divide-base-300 text-sm">
                        <div class="py-3"><dt class="text-base-content/55">User ID</dt><dd class="mt-1 break-all font-mono text-xs">{{$user->id}}</dd></div>
                        <div class="py-3"><dt class="text-base-content/55">Email verified at</dt><dd class="mt-1">{{$user->email_verified_at?->toDayDateTimeString() ?? 'Not verified'}}</dd></div>
                        <div class="py-3"><dt class="text-base-content/55">Theme</dt><dd class="mt-1">{{$user->theme->label()}}</dd></div>
                        <div class="py-3"><dt class="text-base-content/55">Created</dt><dd class="mt-1">{{$user->created_at?->toDayDateTimeString() ?? '—'}}</dd></div>
                        <div class="py-3"><dt class="text-base-content/55">Last updated</dt><dd class="mt-1">{{$user->updated_at?->toDayDateTimeString() ?? '—'}}</dd></div>
                        <div class="py-3"><dt class="text-base-content/55">Remembered login</dt><dd class="mt-1">{{$user->remember_token === null ? 'No' : 'Yes'}}</dd></div>
                    </dl>
                </div>
            </aside>
        </div>

        <section class="mt-6" aria-labelledby="providers-heading">
            <div>
                <h2 id="providers-heading" class="text-xl font-semibold">Authentication providers</h2>
                <p class="mt-1 text-sm text-base-content/70">External accounts connected to this user.</p>
            </div>

            <div class="mt-4 grid gap-4">
                @forelse($user->oauthProviders as $OauthProvider)
                    <article class="card border border-base-300 bg-base-100">
                        <div class="card-body gap-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="avatar"><div class="w-12 rounded-full"><img src="{{$OauthProvider->picture}}" alt="{{$OauthProvider->name}}" referrerpolicy="no-referrer"></div></div>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-semibold">{{Str::headline($OauthProvider->provider_id->value)}}</h3>
                                            @if($OauthProvider->verified_email)<span class="badge badge-success badge-sm">Verified</span>@endif
                                        </div>
                                        <p class="truncate text-sm text-base-content/70">{{$OauthProvider->email}}</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{Admin::userProvider->url([Admin::userParameter => $user->id, Admin::providerParameter => $OauthProvider->getKey()])}}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-ghost btn-sm text-error">Remove provider</button>
                                </form>
                            </div>

                            <dl class="grid gap-x-6 gap-y-3 border-t border-base-300 pt-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                                <div><dt class="text-base-content/55">Provider name</dt><dd>{{$OauthProvider->name}}</dd></div>
                                <div><dt class="text-base-content/55">Given name</dt><dd>{{$OauthProvider->given_name}}</dd></div>
                                <div><dt class="text-base-content/55">Family name</dt><dd>{{$OauthProvider->family_name}}</dd></div>
                                <div><dt class="text-base-content/55">Provider subject</dt><dd class="break-all font-mono text-xs">{{$OauthProvider->sub}}</dd></div>
                                <div><dt class="text-base-content/55">Provider ID</dt><dd class="break-all font-mono text-xs">{{$OauthProvider->id}}</dd></div>
                                <div><dt class="text-base-content/55">Hosted domain</dt><dd>{{$OauthProvider->hd ?? '—'}}</dd></div>
                                <div><dt class="text-base-content/55">Email verified</dt><dd>{{$OauthProvider->email_verified ? 'Yes' : 'No'}}</dd></div>
                                <div><dt class="text-base-content/55">Compatibility verified</dt><dd>{{$OauthProvider->verified_email ? 'Yes' : 'No'}}</dd></div>
                                <div><dt class="text-base-content/55">Profile link</dt><dd class="break-all">{{$OauthProvider->link ?? '—'}}</dd></div>
                                <div class="sm:col-span-2 lg:col-span-3"><dt class="text-base-content/55">Picture URL</dt><dd class="break-all text-xs">{{$OauthProvider->picture}}</dd></div>
                            </dl>
                        </div>
                    </article>
                @empty
                    <div class="rounded-box border border-dashed border-base-300 p-6 text-sm text-base-content/65">No authentication providers connected.</div>
                @endforelse
            </div>
        </section>

        <section class="mt-8 rounded-box border border-error/35 bg-error/5 p-5" aria-labelledby="danger-heading">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 id="danger-heading" class="font-semibold text-error">Delete user</h2>
                    <p class="mt-1 text-sm text-base-content/70">Permanently removes this account and all connected data.</p>
                    @error('delete')<p class="mt-2 text-sm text-error">{{$message}}</p>@enderror
                </div>
                <button type="button" class="btn btn-error" data-delete-dialog-open="delete-user-dialog">Delete user</button>
            </div>
        </section>
    </div>

    <dialog id="delete-user-dialog" class="modal" data-delete-dialog>
        <div class="modal-box">
            <h2 class="text-lg font-semibold">Delete {{$user->name}}?</h2>
            <p class="mt-2 text-sm text-base-content/70">This cannot be undone. Type <strong>delete</strong> to confirm.</p>
            <form method="POST" action="{{$action}}" class="mt-5 space-y-4">
                @csrf
                @method('DELETE')
                <label class="fieldset">
                    <span class="fieldset-legend">Confirmation</span>
                    <input class="input w-full" type="text" name="{{UserDeleteController::confirmation}}" autocomplete="off" data-delete-confirm-input>
                </label>
                <div class="modal-action">
                    <button type="button" class="btn btn-ghost" data-delete-dialog-close>Cancel</button>
                    <button type="submit" class="btn btn-error" data-delete-confirm-submit disabled>Delete user</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop"><button>Close</button></form>
    </dialog>
</x-main>
