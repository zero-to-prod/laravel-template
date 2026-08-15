<?php

namespace App\Modules\Admin\Content;

use App\Helpers\CacheKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

readonly class ContentUpdateController
{
    public function __invoke(Request $Request): RedirectResponse
    {
        $ContentUpdateRequest = ContentUpdateRequest::from($Request->all());
        $Validator = Validator::make(...$ContentUpdateRequest->validator());

        if ($Validator->fails()) {
            return back()->withErrors($Validator);
        }

        Cache::forever(CacheKey::robots->value, $ContentUpdateRequest->robots);
        Cache::forever(CacheKey::llms->value, $ContentUpdateRequest->llms);
        Cache::forever(CacheKey::api_readme->value, $ContentUpdateRequest->api_readme);

        return back()->with('status', 'Site content updated.');
    }
}
