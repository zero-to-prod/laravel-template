<?php

namespace App\Modules\Api\Support;

enum ErrorCode: string
{
    case unauthorized = 'unauthorized';
    case invalid_credentials = 'invalid_credentials';
    case unsupported_media_type = 'unsupported_media_type';
    case token_not_found = 'token_not_found';
    case cache_entry_not_found = 'cache_entry_not_found';
    case cache_lock_not_found = 'cache_lock_not_found';
}
