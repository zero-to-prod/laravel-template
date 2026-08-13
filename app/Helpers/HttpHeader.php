<?php

namespace App\Helpers;

/**
 * The headers this application reads and writes, spelled once.
 *
 * A value is the name exactly as it goes over the wire, because it is used raw and
 * nothing normalizes it. The pair that matters is the one the front end drives: a
 * request that announces itself as a partial swap is answered with a redirect
 * header rather than a redirect, which would otherwise be swapped into the page.
 */
enum HttpHeader: string
{
    case HxRequest = 'HX-Request';
    case HxRedirect = 'HX-Redirect';
    case ContentType = 'Content-Type';
    case Authorization = 'Authorization';
}
