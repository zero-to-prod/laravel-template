<?php

use App\Models\User;
use App\Modules\Admin\Sessions\SessionsQuery;
use App\Routes\Auth;
use App\View\DataModels\SettingsCard;
use Illuminate\Support\Carbon;
use Laravel\Head\Facades\Head;

$User = auth()->user();
$sessions = $User instanceof User ? SessionsQuery::get($User->id) : null;

Head::title('Sessions')
    ->description('Review browsers signed in to your account.')
    ->hiddenFromRobots();
?>
<x-settings-card :settingsCard="[SettingsCard::title => 'Sessions']">
    <x-status-toast/>

    @if($sessions)
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <p class="text-sm text-base-content/70">Review and revoke browsers signed in to your account.</p>
            @if($sessions->isNotEmpty())
                <form method="POST" action="{{Auth::settingsSessions->value}}" onsubmit="return confirm('Sign out every session?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-error btn-outline btn-sm">Clear all sessions</button>
                </form>
            @endif
        </div>

        <div class="mt-4 overflow-x-auto rounded-box border border-base-300">
            <table class="table">
                <thead><tr><th>Last activity</th><th>IP address</th><th>User agent</th><th><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td class="whitespace-nowrap">
                            {{Carbon::createFromTimestamp($session->last_activity)->toDayDateTimeString()}}
                            @if(request()->session()->getId() === $session->id)<span class="badge badge-primary badge-sm ml-1">Current</span>@endif
                        </td>
                        <td class="font-mono text-xs">{{$session->ip_address ?? '—'}}</td>
                        <td class="max-w-md break-words text-sm">{{$session->user_agent ?? '—'}}</td>
                        <td class="text-right">
                            <form method="POST" action="{{Auth::settingsSession->url([Auth::sessionParameter => $session->id])}}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-ghost btn-xs text-error" aria-label="Revoke session {{$session->id}}">Revoke</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-8 text-center text-base-content/65">No sessions found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{$sessions->links()}}</div>
    @endif
</x-settings-card>
