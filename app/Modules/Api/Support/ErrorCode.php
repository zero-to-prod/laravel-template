<?php

namespace App\Modules\Api\Support;

/**
 * The machine-readable reasons an api request failed.
 *
 * A case is the vocabulary the failing response and the test asserting on it share,
 * so neither end spells a literal. The code is returned as both the message and the
 * error, and it is what a client branches on, so a value is part of the contract
 * even though the document types the field as a plain string.
 */
enum ErrorCode: string
{
    case unauthorized = 'unauthorized';
    case missing_ability = 'missing_ability';
    case unsupported_media_type = 'unsupported_media_type';
}
