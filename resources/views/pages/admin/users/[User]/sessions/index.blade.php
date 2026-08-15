<?php

use App\Modules\Admin\Sessions\SessionsQuery;
use App\Routes\Admin;
use Illuminate\Support\Carbon;
use Laravel\Head\Facades\Head;

Head::title('User sessions')
    ->description('Review a registered user\'s active sessions.')
    ->hiddenFromRobots();
?>
<x-main>
    @php($sessions = SessionsQuery::get($user->id))
    <div class="mx-auto max-w-5xl p-4 sm:p-6">
        <a href="{{Admin::user->url([Admin::userParameter => $user->id])}}" class="link link-hover text-sm">← {{$user->name}}</a>

        <header class="mt-4 border-b border-base-300 pb-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-base-content/55">User account</p>
            <h1 class="mt-1 text-2xl font-semibold">Sessions</h1>
            <p class="mt-1 text-sm text-base-content/70">Recent authenticated sessions for {{$user->email}}.</p>
        </header>

        <div class="mt-6 overflow-x-auto rounded-box border border-base-300 bg-base-100">
            <table class="table">
                <thead><tr><th>Last activity</th><th>IP address</th><th>User agent</th><th>Session ID</th></tr></thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td class="whitespace-nowrap">{{Carbon::createFromTimestamp($session->last_activity)->toDayDateTimeString()}}</td>
                            <td class="font-mono text-xs">{{$session->ip_address ?? '—'}}</td>
                            <td class="max-w-md break-words text-sm">{{$session->user_agent ?? '—'}}</td>
                            <td class="max-w-xs break-all font-mono text-xs">{{$session->id}}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-base-content/65">No sessions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{$sessions->links()}}</div>
    </div>
</x-main>
