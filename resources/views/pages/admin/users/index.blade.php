<?php

use App\Modules\Admin\Users\UsersQuery;
use App\Modules\Admin\Users\UsersRequest;
use Laravel\Head\Facades\Head;

Head::title('Users')
    ->description('Every registered user of this application.')
    ->hiddenFromRobots();

$UsersRequest = UsersRequest::of(request());
?>
<x-main>
    <div class="mx-auto max-w-5xl p-4 sm:p-6">
        <h1 class="text-2xl font-semibold">Users</h1>

        <x-users-table :usersTable="$UsersRequest->table(UsersQuery::get($UsersRequest))"/>
    </div>
</x-main>
