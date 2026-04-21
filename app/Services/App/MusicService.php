<?php

namespace App\Services\App;

use App\Models\Music;
use App\Models\Category;
use App\Services\Json\JsonResponse;
use App\Services\App\SendMailService;
use App\Services\Cloudinary\CloudinarySignedDeliveryService;
use App\Models\Otp;

class MusicService
{
    public function __construct(
        private SendMailService $sendMailService,
        private CloudinarySignedDeliveryService $cloudinarySignedDelivery,
    ) {
    }

    public function get_music(?string $category = null)
    {
        # If there's no search query, fetch every data from the category collection
        if ($category === null || $category === '') {
            $music = Music::all();
            return $this->musicFetchedResponse($music);
        }

        # Fetch all
        if ($category === 'all') {
            $music = Music::all();
            return $this->musicFetchedResponse($music);
        }

        # If category is fetch, fetch by the category's name
        $music = Music::where("category", $category)->get();
        return $this->musicFetchedResponse($music);
    }

    /**
     * Refresh Cloudinary authenticated delivery URLs on each list fetch and expose
     * expiry metadata so the client can refetch before URLs go stale.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Music>|\Illuminate\Support\Collection<int, Music>  $music
     */
    private function musicFetchedResponse($music)
    {
        $payload = [
            'status' => 200,
            'success' => true,
            'message' => 'Music fetched',
            'data' => $this->mapMusicWithFreshCloudinaryUrls($music),
        ];

        if ($this->cloudinarySignedDelivery->isConfigured()) {
            $ttl = $this->cloudinarySignedDelivery->ttlSeconds();
            $payload['media_urls_expires_at'] = now()->addSeconds($ttl)->toIso8601String();
            $payload['media_urls_ttl_seconds'] = $ttl;
        }

        return response()->json($payload, 200);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Music>|\Illuminate\Support\Collection<int, Music>  $music
     * @return array<int, array<string, mixed>>
     */
    private function mapMusicWithFreshCloudinaryUrls($music): array
    {
        return $music->map(function (Music $m) {
            $row = $m->toArray();

            $filename = isset($row['filename']) && is_string($row['filename']) ? trim($row['filename']) : '';
            if ($filename !== '' && $this->cloudinarySignedDelivery->isConfigured()) {
                $signed = $this->cloudinarySignedDelivery->signAuthenticatedVideo($filename);
                if ($signed !== null) {
                    $row['music_url'] = $signed;
                }
            }

            $thumb = isset($row['thumbnail_url']) && is_string($row['thumbnail_url']) ? trim($row['thumbnail_url']) : '';
            if ($thumb !== '' && $this->cloudinarySignedDelivery->isConfigured()) {
                $parsed = $this->cloudinarySignedDelivery->parseAuthenticatedDeliveryUrl($thumb);
                if ($parsed !== null) {
                    $signedThumb = $parsed['resource_type'] === 'video'
                        ? $this->cloudinarySignedDelivery->signAuthenticatedVideo($parsed['public_id'])
                        : $this->cloudinarySignedDelivery->signAuthenticatedImage($parsed['public_id']);
                    if ($signedThumb !== null) {
                        $row['thumbnail_url'] = $signedThumb;
                    }
                }
            }

            return $row;
        })->values()->all();
    }

    # Ask for permission before adding music
    public function init_create_music()
    {
        $otp = (string) random_int(100000, 999999);

        Otp::create([
            "otp_code" => $otp,
            "otp_type" => "permission"
        ]);

        $this->sendMailService->sendOtpMail('sample@gmail.com', $otp, 'Permission to add music');
        return JsonResponse::success('Permission sent', null);
    }

    # Save music
    public function save_music(array $data)
    {
        # use ->get() to fetch multiple data with filter, this returns [] "array/list-like collection"
        # use ->first() to get 1 item, this returns {} "single object/model"
        # use ->exists() to also get 1 item, this return true/false "boolean"

        $does_category_exist = Category::where("title", $data['category'])->exists();

        if (!$does_category_exist) {
            return JsonResponse::not_found("Music category does not exist");
        }

        $create_music = Music::create($data);
        return JsonResponse::created("Music added", $create_music);
    }

    public function edit_music_category(array $data)
    {

        # Check if the category exist, this returns a boolean "true/false"
        $verify_category = Category::where("title", $data["category"])->exists();

        if ($verify_category) {
            return JsonResponse::not_found("Category does not exist", null);
        }

        # Find category and id from the music model using eloquent
        $music = Music::where("category", $data["category"])->where("id", $data["id"])->first();

        if (!$music) {
            return JsonResponse::not_found("Record not found", null);
        }

        $music->update([
            "category" => $data["new_category"]
        ]);

        return JsonResponse::success("Category updated", null);
    }

    public function enable_disable_music(array $data)
    {
        $music = Music::where("id", $data["id"])->first();

        if (!$music) {
            return JsonResponse::not_found("Record not found", null);
        }

        $music->update([
            "disabled" => $data["disabled"]
        ]);
    }
}

?>
