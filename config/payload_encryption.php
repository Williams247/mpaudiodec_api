<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application-layer AES-256-GCM key (shared with the frontend)
    |--------------------------------------------------------------------------
    |
    | Must be the base64 encoding of exactly 32 bytes, e.g.:
    | php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
    |
    */

    'key' => env('API_PAYLOAD_ENCRYPTION_KEY'),

];
