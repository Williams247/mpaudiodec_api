<?php

namespace App\Services\Cloudinary;

use Cloudinary\Asset\Image;
use Cloudinary\Asset\Video;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Configuration\UrlConfig;
use Throwable;

/**
 * Regenerates signed HTTPS delivery URLs for authenticated Cloudinary assets.
 */
class CloudinarySignedDeliveryService
{
    private ?Cloudinary $client = null;

    public function __construct()
    {
        $url = (string) config('cloudinary.url', '');
        $cloudName = (string) config('cloudinary.cloud_name', '');
        $apiKey = (string) config('cloudinary.api_key', '');
        $apiSecret = (string) config('cloudinary.api_secret', '');

        try {
            if ($url !== '') {
                $this->client = new Cloudinary(Configuration::fromCloudinaryUrl($url));
            } elseif ($cloudName !== '' && $apiKey !== '' && $apiSecret !== '') {
                $this->client = new Cloudinary([
                    'cloud' => [
                        'cloud_name' => $cloudName,
                        'api_key' => $apiKey,
                        'api_secret' => $apiSecret,
                    ],
                ]);
            }
        } catch (Throwable) {
            $this->client = null;
        }
    }

    public function isConfigured(): bool
    {
        return $this->client !== null;
    }

    public function ttlSeconds(): int
    {
        return max(60, (int) config('cloudinary.signed_url_ttl_seconds', 3600));
    }

    public function signAuthenticatedVideo(string $publicId): ?string
    {
        if ($this->client === null) {
            return null;
        }

        try {
            $asset = Video::authenticated($publicId, $this->client->configuration);
            $asset->urlConfig->signUrl = true;
            $asset->urlConfig->secure = true;
            $asset->urlConfig->setUrlConfig(UrlConfig::ANALYTICS, false);

            return (string) $asset->toUrl();
        } catch (Throwable $e) {
            \Log::warning('Cloudinary authenticated video URL failed', [
                'public_id' => $publicId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function signAuthenticatedImage(string $publicId): ?string
    {
        if ($this->client === null) {
            return null;
        }

        try {
            $asset = Image::authenticated($publicId, $this->client->configuration);
            $asset->urlConfig->signUrl = true;
            $asset->urlConfig->secure = true;
            $asset->urlConfig->setUrlConfig(UrlConfig::ANALYTICS, false);

            return (string) $asset->toUrl();
        } catch (Throwable $e) {
            \Log::warning('Cloudinary authenticated image URL failed', [
                'public_id' => $publicId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array{resource_type: string, public_id: string}|null
     */
    public function parseAuthenticatedDeliveryUrl(string $url): ?array
    {
        $url = trim($url);
        if ($url === '' || ! str_contains($url, 'res.cloudinary.com')) {
            return null;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        if ($path === '') {
            return null;
        }

        if (preg_match('#/(image|video)/authenticated/s--[^/]+--/(?:v\d+/)?(.+)$#', $path, $m)) {
            return [
                'resource_type' => $m[1],
                'public_id' => rawurldecode($m[2]),
            ];
        }

        return null;
    }
}
