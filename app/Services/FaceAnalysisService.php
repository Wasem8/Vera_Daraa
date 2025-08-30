<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FaceAnalysisService
{
    private string $apiKey;
    private string $apiSecret;
    private string $apiUrl = 'https://api-us.faceplusplus.com/facepp/v3/detect';

    public function __construct()
    {
        $this->apiKey = config('services.facepp.api_key') ?? '';
        $this->apiSecret = config('services.facepp.api_secret') ?? '';

        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new \Exception("Face++ API credentials are missing. Please check your .env file.");
        }
    }

    public function analyzeFace(string $imagePath): array
    {
        $response = Http::asMultipart()->post($this->apiUrl, [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'image_file' => fopen($imagePath, 'r'),
            'return_attributes' => 'gender,age,beauty,skinstatus'
        ]);

        return $response->json();
    }

    /**
     * تحليل النتائج وإرجاع نصائح مناسبة لخدمات المركز
     */
    public function analyzeAndRecommend(string $imagePath): array
    {
        $data = $this->analyzeFace($imagePath);

        if (!isset($data['faces']) || empty($data['faces'])) {
            return [
                'status' => 0,
                'message' => 'لم يتم العثور على وجه في الصورة.'
            ];
        }

        $results = [];

        foreach ($data['faces'] as $face) {
            $attributes = $face['attributes'];

            $age = $attributes['age']['value'];
            $gender = $attributes['gender']['value'];
            $skin = $attributes['skinstatus'];

            $recommendations = [];

            if ($skin['acne'] > 50) {
                $recommendations[] = "نوصي بجلسة تنظيف بشرة للتقليل من حب الشباب.";
            }

            if ($skin['dark_circle'] > 50) {
                $recommendations[] = "نوصي بعلاج للهالات السوداء حول العين.";
            }

            if ($skin['stain'] > 50) {
                $recommendations[] = "نوصي بجلسة تفتيح للبقع والتصبغات.";
            }

            if ($skin['health'] < 0.5) {
                $recommendations[] = "نوصي بجلسة ترطيب وعناية عامة بالبشرة.";
            }

            $results[] = [
                'age' => $age,
                'gender' => $gender,
                'skin_status' => $skin,
                'recommendations' => $recommendations
            ];
        }

        return [
            'status' => 1,
            'data' => $results
        ];
    }

}
