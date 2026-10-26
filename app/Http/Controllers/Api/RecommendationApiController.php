<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationApiController extends Controller
{
    protected RecommendationService $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Get personalized product recommendations.
     */
    public function personalized(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 8);
        $lat = $request->has('lat') ? (float) $request->get('lat') : null;
        $lng = $request->has('lng') ? (float) $request->get('lng') : null;

        $products = $this->recommendationService->getPersonalizedRecommendations(
            $request->user('sanctum') ?? $request->user(),
            $limit,
            $lat,
            $lng
        );

        return response()->json([
            'success' => true,
            'count' => $products->count(),
            'recommendations' => $products,
        ]);
    }

    /**
     * Get similar products for a given product ID.
     */
    public function similar(int $id, Request $request): JsonResponse
    {
        $product = Product::active()->findOrFail($id);
        $limit = (int) $request->get('limit', 4);

        $similar = $this->recommendationService->getSimilarProducts($product, $limit);

        return response()->json([
            'success' => true,
            'product_id' => $id,
            'similar_products' => $similar,
        ]);
    }

    /**
     * Get items frequently bought together.
     */
    public function frequentlyBoughtTogether(int $id, Request $request): JsonResponse
    {
        $product = Product::active()->findOrFail($id);
        $limit = (int) $request->get('limit', 3);

        $items = $this->recommendationService->getFrequentlyBoughtTogether($product, $limit);

        return response()->json([
            'success' => true,
            'product_id' => $id,
            'frequently_bought_together' => $items,
        ]);
    }
}
