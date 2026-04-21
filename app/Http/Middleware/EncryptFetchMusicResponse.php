<?php

namespace App\Http\Middleware;

use App\Services\Crypto\PayloadEncryptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wraps successful JSON responses for GET /fetch-music in { "audio": "<base64>" }.
 * If API_PAYLOAD_ENCRYPTION_KEY is unset, returns the original JSON (for local dev).
 */
class EncryptFetchMusicResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $service = PayloadEncryptionService::fromBase64Key(config('payload_encryption.key'));
        if ($service === null) {
            return $response;
        }

        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'application/json')) {
            return $response;
        }

        $raw = $response->getContent();
        if ($raw === '' || $raw === false) {
            return $response;
        }

        try {
            $encrypted = $service->encrypt($raw);
        } catch (\Throwable) {
            return $response;
        }

        return response()->json([
            'audio' => $encrypted,
        ], 200, [
            'Content-Type' => 'application/json',
            'X-Payload-Encrypted' => '1',
        ]);
    }
}
