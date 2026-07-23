<?php

use Illuminate\Http\Request;

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Set trusted proxies to '*', or specify your proxy IPs as an array.
    |
    */

    'proxies' => env('TRUSTED_PROXIES', '*'),

    /*
    |--------------------------------------------------------------------------
    | Headers to Trust
    |--------------------------------------------------------------------------
    |
    | These are the headers used to detect proxies and their forwarded values.
    | Laravel 9 requires combining individual HEADER_X_FORWARDED_* constants.
    |
    */

    'headers' => Request::HEADER_X_FORWARDED_FOR
               | Request::HEADER_X_FORWARDED_HOST
               | Request::HEADER_X_FORWARDED_PORT
               | Request::HEADER_X_FORWARDED_PROTO
               | Request::HEADER_X_FORWARDED_AWS_ELB,

];
