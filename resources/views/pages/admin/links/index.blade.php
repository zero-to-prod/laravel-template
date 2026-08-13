<?php

use App\Routes\AdminLink;
use Illuminate\Support\Str;
use Laravel\Head\Facades\Head;

Head::title('Links')
    ->description('Every route marked as a link an admin reads.')
    ->hiddenFromRobots();
?>
<x-main>
    <div class="card card-compact m-auto max-w-3xl sm:mt-24">
        <div class="card-body">
            <h1 class="card-title">Links</h1>

            <ul class="mt-4 divide-y divide-base-300">
                @foreach(AdminLink::routes() as $link)
                    <li class="flex items-center justify-between gap-4 py-2">
                        <span class="text-sm font-medium">{{Str::headline($link[AdminLink::name])}}</span>
                        <a href="{{$link[AdminLink::url]}}" target="_blank" class="link link-primary font-mono text-sm">
                            {{$link[AdminLink::url]}}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-main>
