<?php

namespace App\Services;

class DosenService
{
    /**
     * Search dosen by name, nidn, or kode from SIAKAD API
     * @param string $search
     * @return array
     */
    public static function searchDosen($search = '')
    {
        $apiKey = config('simkeu.simkeu_api_key');
        $url = config('simkeu.simkeu_url') . "dosen-staff";
        
        if (!empty($search)) {
            $url .= "?search=" . urlencode($search);
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $apiKey",
            "Accept: application/json"
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        \Illuminate\Support\Facades\Log::info("DosenService URL: $url");
        \Illuminate\Support\Facades\Log::info("DosenService HTTP Code: $httpcode");
        \Illuminate\Support\Facades\Log::info("DosenService Error: $err");
        \Illuminate\Support\Facades\Log::info("DosenService Response: $response");

        $decoded = json_decode($response);
        if (is_object($decoded) && isset($decoded->data)) {
            return $decoded->data;
        }
        return [];
    }
}
