<?php

namespace App\Http\Middleware;

use App\Services\Crypto\PayloadEncryptionService;
use App\Services\Json\JsonResponse;
use Closure;
use Illuminate\Http\Request;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * When the JSON body contains "encryptedPayload" (base64 AES-256-GCM blob),
 * decrypts to a JSON object and merges fields so controllers/validators see the usual shape.
 * Requests without "encryptedPayload" pass through unchanged.
 */
class DecryptApplicationPayload
{
    public function handle(Request $request, Closure $next): Response
    {
        $wrapped = $request->input('encryptedPayload');

        if ($wrapped === null || $wrapped === '') {
            return $next($request);
        }

        if (!is_string($wrapped)) {
            return JsonResponse::bad_request('encryptedPayload must be a string.', null);
        }

        $service = PayloadEncryptionService::fromBase64Key(config('payload_encryption.key'));
        if ($service === null) {
            return response()->json([
                'status' => 503,
                'success' => false,
                'message' => 'Payload encryption is not configured on the server.',
                'data' => null,
            ], 503);
        }

        try {
            $plain = $service->decrypt($wrapped);
            $data = json_decode($plain, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return JsonResponse::bad_request('Decrypted payload is not valid JSON.', null);
        } catch (Throwable) {
            return JsonResponse::bad_request('Could not decrypt payload.', null);
        }

        if (!is_array($data)) {
            return JsonResponse::bad_request('Decrypted payload must be a JSON object.', null);
        }

        $request->merge($data);
        $request->request->remove('encryptedPayload');

        return $next($request);
    }
}
