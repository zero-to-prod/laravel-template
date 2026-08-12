<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Contact Details
    |--------------------------------------------------------------------------
    |
    | What the contact page shows. `support_email` is the address it invites
    | people to write to, and is the only required value: the page renders
    | whatever is set here. `address` is optional and is omitted from the page
    | when it is empty.
    |
    */

    'support_email' => env('COMPANY_SUPPORT_EMAIL', (string) env('MAIL_FROM_ADDRESS', 'hello@example.com')),

    'response_time' => env('COMPANY_RESPONSE_TIME', 'two business days'),

    'address' => env('COMPANY_ADDRESS'),

    /*
    |--------------------------------------------------------------------------
    | Legal
    |--------------------------------------------------------------------------
    |
    | The governing law clause of the terms of service. `jurisdiction` is the
    | body of law the terms are read under, and `venue` is where a dispute is
    | heard. The defaults are placeholders that render verbatim on the page:
    | set both before publishing the terms.
    |
    */

    'jurisdiction' => env('COMPANY_JURISDICTION', '[jurisdiction]'),

    'venue' => env('COMPANY_VENUE', '[venue]'),

];
