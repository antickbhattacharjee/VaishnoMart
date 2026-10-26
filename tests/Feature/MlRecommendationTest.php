<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\RecommendationService;
use Tests\TestCase;

class MlRecommendationTest extends TestCase
{
    public function test_recommendation_service_trains_model_successfully(): void
    {
        $service = app(RecommendationService::class);
        $result = $service->train();

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['products_count']);
        $this->assertFileExists(storage_path('app/ml/recommendation_model.json'));
    }

    public function test_personalized_recommendations_return_ranked_products_with_match_scores(): void
    {
        $service = app(RecommendationService::class);
        $customer = User::where('role', User::ROLE_CUSTOMER)->first();

        $recommendations = $service->getPersonalizedRecommendations($customer, 6);

        $this->assertNotEmpty($recommendations);
        $this->assertLessThanOrEqual(6, $recommendations->count());

        $first = $recommendations->first();
        $this->assertNotNull($first->match_percentage);
        $this->assertNotNull($first->recommendation_reason);
        $this->assertGreaterThanOrEqual(80, $first->match_percentage);
    }

    public function test_similar_products_calculation_returns_category_and_content_matches(): void
    {
        $service = app(RecommendationService::class);
        $product = Product::active()->first();

        $this->assertNotNull($product);
        $similar = $service->getSimilarProducts($product, 4);

        $this->assertNotEmpty($similar);
        $this->assertFalse($similar->contains('id', $product->id));
    }

    public function test_recommendations_api_endpoint(): void
    {
        $product = Product::active()->first();

        // Personalized API
        $response = $this->getJson('/api/recommendations/personalized');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'count', 'recommendations']);

        // Similar products API
        $simResponse = $this->getJson("/api/recommendations/product/{$product->id}/similar");
        $simResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('product_id', $product->id);
    }

    public function test_home_page_displays_ai_recommendations(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Recommended For You');
        $response->assertSee('AI & ML Powered', false);
    }
}
