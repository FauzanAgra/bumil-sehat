<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Set TRUSTED_PROXIES di .env untuk reverse proxy tepercaya (IP/CIDR
    | dipisahkan koma, atau "*" jika di belakang proxy cloud terkontrol).
    | Biarkan kosong di lokal bila tidak berada di balik reverse proxy.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
