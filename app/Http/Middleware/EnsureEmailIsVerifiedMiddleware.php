<?php

namespace App\Http\Middleware;

use App\Helpers\HttpHeader;
use App\Routes\Auth;
use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class EnsureEmailIsVerifiedMiddleware extends EnsureEmailIsVerified
{
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        $User = $request->user();
        $verified = $User && (! $User instanceof MustVerifyEmail || $User->hasVerifiedEmail());

        if ($verified) {
            return $next($request);
        }

        if ($request->hasHeader(HttpHeader::HxRequest->value)) {
            return response()->noContent(403)->header(HttpHeader::HxRedirect->value, Auth::verificationNotice->value);
        }

        return parent::handle($request, $next, $redirectToRoute);
    }
}
