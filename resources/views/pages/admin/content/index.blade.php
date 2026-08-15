<?php

use App\Helpers\CacheKey;
use App\Modules\Admin\Content\ContentUpdateRequest;
use App\Routes\Admin;
use Illuminate\Support\Facades\Cache;
use Laravel\Head\Facades\Head;

Head::title('Site content')
    ->description('Edit the public crawler and API documentation files.')
    ->hiddenFromRobots();
?>
<x-main>
    <div class="mx-auto max-w-5xl p-4 sm:p-6">
        <header class="border-b border-base-300 pb-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-base-content/55">Administration</p>
            <h1 class="mt-1 text-2xl font-semibold">Site content</h1>
            <p class="mt-1 text-sm text-base-content/70">Edit the content served to crawlers, AI agents, and API consumers.</p>
        </header>

        <x-status-toast/>

        <form class="mt-6 space-y-6" method="POST" action="{{Admin::content->value}}">
            @csrf

            <section class="card border border-base-300 bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">{{CacheKey::robots->value}}</h2>
                    <p class="text-sm text-base-content/70">Crawler directives. The sitemap URL is appended automatically.</p>
                    <textarea class="textarea h-40 w-full font-mono text-sm" name="{{ContentUpdateRequest::robots}}" required>{{old(ContentUpdateRequest::robots, Cache::get(CacheKey::robots->value, static fn (): string => (string) file_get_contents(resource_path(CacheKey::robots->value))))}}</textarea>
                    @error(ContentUpdateRequest::robots)<p class="text-sm text-error">{{$message}}</p>@enderror
                </div>
            </section>

            <section class="card border border-base-300 bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">{{CacheKey::llms->value}}</h2>
                    <p class="text-sm text-base-content/70">Markdown overview for AI agents and language models.</p>
                    <textarea class="textarea h-96 w-full font-mono text-sm" name="{{ContentUpdateRequest::llms}}" required>{{old(ContentUpdateRequest::llms, Cache::get(CacheKey::llms->value, static fn (): string => (string) file_get_contents(resource_path(CacheKey::llms->value))))}}</textarea>
                    @error(ContentUpdateRequest::llms)<p class="text-sm text-error">{{$message}}</p>@enderror
                </div>
            </section>

            <section class="card border border-base-300 bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">{{CacheKey::api_readme->value}}</h2>
                    <p class="text-sm text-base-content/70">Markdown returned by the public API readme endpoint.</p>
                    <textarea class="textarea h-96 w-full font-mono text-sm" name="{{ContentUpdateRequest::api_readme}}" required>{{old(ContentUpdateRequest::api_readme, Cache::get(CacheKey::api_readme->value, static fn (): string => (string) file_get_contents(resource_path(CacheKey::api_readme->value))))}}</textarea>
                    @error(ContentUpdateRequest::api_readme)<p class="text-sm text-error">{{$message}}</p>@enderror
                </div>
            </section>

            <div class="flex justify-end">
                <button class="btn btn-primary">Save content</button>
            </div>
        </form>
    </div>
</x-main>
