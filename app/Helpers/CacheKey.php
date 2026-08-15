<?php

namespace App\Helpers;

/** Stores the cache keys used in the application. */
enum CacheKey: string
{
    case robots = 'robots.txt';
    case llms = 'llms.txt';
    case api_readme = 'api-readme.md';
}
