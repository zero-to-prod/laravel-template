<?php

namespace App\Http\Middleware;

use App\Helpers\Gravatar;
use App\Helpers\SessionKey;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class CacheUserPicture
{
    public function handle(Request $Request, Closure $Closure): Response
    {
        $key = SessionKey::user_picture->value;
        $User = $Request->user();

        if ($User instanceof User && ! $Request->session()->has($key)) {
            $Request->session()->put($key, Gravatar::image($User->email) ?? '');
        }

        return $Closure($Request);
    }
}
