<?php

namespace App\Helpers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final readonly class Gravatar
{
    public static function url(string $email): string
    {
        return 'https://www.gravatar.com/avatar/'.hash('sha256', strtolower(trim($email))).'?s=80&d=404&r=g';
    }

    public static function image(string $email): ?string
    {
        try {
            $Response = Http::timeout(3)->get(self::url($email));
        } catch (ConnectionException) {
            return null;
        }

        $content_type = $Response->header('Content-Type');

        if (! $Response->successful() || ! str_starts_with($content_type, 'image/')) {
            return null;
        }

        return 'data:'.$content_type.';base64,'.base64_encode($Response->body());
    }
}
