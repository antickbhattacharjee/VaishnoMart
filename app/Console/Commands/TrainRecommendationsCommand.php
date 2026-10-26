<?php

namespace App\Console\Commands;

use App\Services\RecommendationService;
use Illuminate\Console\Command;

class TrainRecommendationsCommand extends Command
{
    protected $signature = 'ml:train-recommendations';
    protected $description = 'Train and compile Machine Learning product recommendation matrices (TF-IDF, Cosine Similarity & Co-occurrence)';

    public function handle(RecommendationService $recommendationService): int
    {
        $this->info('Starting Machine Learning recommendation model training...');
        $startTime = microtime(true);

        $result = $recommendationService->train();

        $duration = round((microtime(true) - $startTime), 3);

        $this->info("✓ Model trained successfully using [{$result['engine']}] in {$duration}s");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Engine', $result['engine']],
                ['Active Products Processed', $result['products_count']],
                ['Historical Order Baskets Processed', $result['baskets_count']],
                ['Output Artifact', storage_path('app/ml/recommendation_model.json')],
            ]
        );

        return self::SUCCESS;
    }
}
