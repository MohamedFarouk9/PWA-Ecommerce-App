<?php

namespace App\Console\Commands\Elasticsearch;

use App\Models\Product;
use App\Services\Search\ElasticsearchService;
use Illuminate\Console\Command;

class IndexProductsCommand extends Command
{
    protected $signature = 'elasticsearch:index-products 
                            {--fresh : Drop and recreate index before indexing}
                            {--chunk=500 : Number of records per bulk chunk}';

    protected $description = 'Index all published products into Elasticsearch with autocomplete analyzers';

    public function handle(ElasticsearchService $esService): int
    {
        $this->info('Connecting to Elasticsearch...');

        if (!$esService->ping()) {
            $this->error('Unable to reach Elasticsearch cluster at ' . config('elasticsearch.host'));
            $this->warn('Ensure Elasticsearch is running in Docker (docker-compose up -d elasticsearch) or locally on port 9200.');
            return 1;
        }

        $this->info('Elasticsearch cluster is healthy.');

        $isFresh = $this->option('fresh');
        $this->info($isFresh ? 'Re-creating index and mappings...' : 'Ensuring index exists...');

        if (!$esService->createIndexIfNotExists($isFresh)) {
            $this->error('Failed to create or verify Elasticsearch index.');
            return 1;
        }

        $chunkSize = (int) $this->option('chunk');
        $query = Product::with(['category', 'status'])
            ->whereHas('status', function ($q) {
                $q->where('name', 'published');
            });

        $total = $query->count();
        $this->info("Found {$total} published products to index.");

        if ($total === 0) {
            $this->warn('No published products found in database.');
            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $indexedTotal = 0;

        $query->chunk($chunkSize, function ($products) use ($esService, $bar, &$indexedTotal) {
            $indexed = $esService->bulkIndex($products);
            $indexedTotal += $indexed;
            $bar->advance($products->count());
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Successfully indexed {$indexedTotal} / {$total} products into Elasticsearch.");

        return 0;
    }
}
