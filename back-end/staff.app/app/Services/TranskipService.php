<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranskipService
{
    private const CACHE_TTL = 3600; // 1 jam

    /**
     * Get nilai by NIM — with cache + NIM cleanup fallback
     */
    public static function getNilaiByNim(string $nim): array
    {
        $cacheKey = 'siakad_nilai_' . md5($nim);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($nim) {
            $data = self::fetchFromSiakad($nim);

            // Fallback: coba NIM tanpa titik
            if (empty($data) && str_contains($nim, '.')) {
                $cleanNim = str_replace('.', '', $nim);
                Log::info("SIAKAD fallback NIM tanpa titik: {$cleanNim}");
                $data = self::fetchFromSiakad($cleanNim);
            }

            return $data;
        });
    }

    /**
     * Clear cache untuk NIM tertentu
     */
    public static function clearCache(string $nim): void
    {
        Cache::forget('siakad_nilai_' . md5($nim));
    }

    /**
     * Fetch data dari SIAKAD API
     */
    private static function fetchFromSiakad(string $nim): array
    {
        $apiKey = config('simkeu.simkeu_api_key');
        $baseUrl = config('simkeu.simkeu_url');

        $response = Http::withHeaders([
                'apikey' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout(10)
            ->connectTimeout(5)
            ->retry(2, 500)
            ->get($baseUrl . 'transkrip-staff', [
                'nim' => $nim,
            ]);

        if ($response->successful()) {
            $body = $response->json();
            return $body['data'] ?? [];
        }

        Log::warning("SIAKAD API gagal untuk NIM: {$nim}", [
            'status' => $response->status(),
        ]);

        return [];
    }
}
