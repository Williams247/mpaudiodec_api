<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary URL (optional)
    |--------------------------------------------------------------------------
    |
    | Full CLOUDINARY_URL from the dashboard (cloudinary://api_key:secret@cloud_name).
    | If set, individual keys below are ignored.
    |
    */

    'url' => env('CLOUDINARY_URL'),

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Signed delivery URL window
    |--------------------------------------------------------------------------
    |
    | Used when regenerating authenticated delivery URLs on GET /fetch-music.
    | The API returns media_urls_expires_at = now + this many seconds so the
    | frontend can refetch before URLs become invalid. Keep in sync with upload
    | service signing TTL if possible.
    |
    */

    // Prefer same env name as mpaudiodec_upload (CLOUDINARY_SIGNED_URL_EXPIRES), with API-specific alias.
    'signed_url_ttl_seconds' => max(60, (int) (env('CLOUDINARY_SIGNED_URL_EXPIRES') ?: env('CLOUDINARY_SIGNED_URL_TTL_SECONDS', 3600))),

];
