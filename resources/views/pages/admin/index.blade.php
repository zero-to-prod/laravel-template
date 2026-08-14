<?php

use App\Models\User;
use App\Sources\Db\App\Users;
use Laravel\Head\Facades\Head;

Head::title('Admin')
    ->description('Administer this application.')
    ->hiddenFromRobots();
?>
<x-main>
    <div class="card card-compact m-auto max-w-3xl sm:mt-24">
        <div class="card-body">
            <h1 class="card-title">Admin</h1>

            <p class="text-sm text-base-content/70">
                This page is only reachable by a signed-in user holding the admin role.
            </p>

            <dl class="mt-4 stats stats-vertical sm:stats-horizontal shadow">
                <div class="stat">
                    <dt class="stat-title">Registered users</dt>
                    <dd class="stat-value text-2xl">{{User::count()}}</dd>
                </div>
                <div class="stat">
                    <dt class="stat-title">Verified users</dt>
                    <dd class="stat-value text-2xl">
                        {{User::whereNotNull(Users::email_verified_at->value)->count()}}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</x-main>
