<?php

namespace App\Http\Middleware;

use App\Helpers\HttpVerb;
use App\Models\User;
use App\Modules\Api\Support\ErrorCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenAbilityMiddleware
{
    public function handle(Request $Request, Closure $Closure): Response
    {
        $User = $Request->user();
        $ability = HttpVerb::of($Request)->ability($Request->route()->uri());

        if ($User instanceof User && ! $User->tokenCan($ability)) {
            return api_response()->forbidden(ErrorCode::missing_ability);
        }

        return $Closure($Request);
    }
}
