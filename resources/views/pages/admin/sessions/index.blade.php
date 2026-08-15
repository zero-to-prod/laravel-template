<?php

use App\Models\User;
use App\Modules\Admin\Sessions\SessionsQuery;
use App\Routes\Admin;
use Illuminate\Support\Carbon;
use Laravel\Head\Facades\Head;

Head::title('Sessions')
    ->description('Review active user sessions.')
    ->hiddenFromRobots();
?>
<x-main>
    @php
        $userId = request()->query(Admin::userParameter);
        $User = is_string($userId) ? User::query()->findOrFail($userId) : null;
        $email = request()->string('email')->trim()->toString();
        $sessions = SessionsQuery::get($User?->id, $email);
    @endphp
    <div class="mx-auto max-w-6xl p-4 sm:p-6">
        <header class="border-b border-base-300 pb-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-base-content/55">Administration</p>
            <h1 class="mt-1 text-2xl font-semibold">Sessions</h1>
            @if($User)
                <p class="mt-1 text-sm text-base-content/70">
                    Sessions for <a class="link" href="{{Admin::user->url([Admin::userParameter => $User->id])}}">{{$User->email}}</a>.
                    <a class="link ml-2" href="{{Admin::sessions->value}}">Show all</a>
                </p>
            @else
                <p class="mt-1 text-sm text-base-content/70">Recent authenticated sessions across all users.</p>
            @endif
        </header>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <form method="GET" action="{{Admin::sessions->value}}" class="flex w-full max-w-xl gap-2">
                <label class="form-control w-full">
                    <span class="label-text mb-1 text-sm font-medium">Search by email</span>
                    <input type="search" name="email" value="{{$email}}" placeholder="user@example.com" class="input input-bordered w-full"/>
                </label>
                <button class="btn btn-primary self-end">Search</button>
            </form>

            @if($User)
                <form method="POST" action="{{Admin::sessions->value}}" onsubmit="return confirm('Clear every session for {{$User->email}}?')">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="{{Admin::userParameter}}" value="{{$User->id}}"/>
                    <button class="btn btn-error btn-outline whitespace-nowrap">Clear all sessions</button>
                </form>
            @endif
        </div>

        <div class="mt-6 overflow-x-auto rounded-box border border-base-300 bg-base-100">
            <table class="table">
                <thead><tr><th>Last activity</th><th>User</th><th>IP address</th><th>User agent</th><th>Session ID</th><th><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td class="whitespace-nowrap">{{Carbon::createFromTimestamp($session->last_activity)->toDayDateTimeString()}}</td>
                            <td>
                                @if($session->user_id)
                                    <a class="link text-sm" href="{{Admin::user->url([Admin::userParameter => $session->user_id])}}">{{$session->email}}</a>
                                @else
                                    Guest
                                @endif
                            </td>
                            <td class="font-mono text-xs">{{$session->ip_address ?? '—'}}</td>
                            <td class="max-w-md break-words text-sm">{{$session->user_agent ?? '—'}}</td>
                            <td class="max-w-xs break-all font-mono text-xs">{{$session->id}}</td>
                            <td class="text-right">
                                <form method="POST" action="{{Admin::session->url([Admin::sessionParameter => $session->id])}}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-ghost btn-xs text-error" aria-label="Revoke session {{$session->id}}">Revoke</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-base-content/65">No sessions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{$sessions->withQueryString()->links()}}</div>
    </div>
</x-main>
