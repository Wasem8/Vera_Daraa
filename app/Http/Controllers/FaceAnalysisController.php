<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FaceAnalysisService;

class FaceAnalysisController extends Controller
{
    protected FaceAnalysisService $faceService;

    public function __construct(FaceAnalysisService $faceService)
    {
        $this->faceService = $faceService;
    }

    public function analyze(Request $request, FaceAnalysisService $faceAnalysisService)
    {
        $request->validate([
            'image' => 'required|image|max:5000'
        ]);

        $path = $request->file('image')->getPathname();

        $result = $faceAnalysisService->analyzeAndRecommend($path);

        return response()->json($result);
    }

}
