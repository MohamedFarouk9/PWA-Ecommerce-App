<?php

namespace App\Services\Search;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ElasticsearchService
{
    protected string $host;
    protected string $index;
    protected float $timeout;
    protected bool $enabled;
    protected ?string $user;
    protected ?string $pass;

    public function __construct()
    {
        $this->host = rtrim(config('elasticsearch.host', 'http://127.0.0.1:9200'), '/');
        $this->index = config('elasticsearch.index', 'ecommerce_products');
        $this->timeout = (float) config('elasticsearch.timeout', 2.0);
        $this->enabled = (bool) config('elasticsearch.enabled', true);
        $this->user = config('elasticsearch.user');
        $this->pass = config('elasticsearch.pass');
    }

    /**
     * Build an authenticated HTTP client instance
     */
    protected function client()
    {
        $request = Http::timeout($this->timeout)->acceptJson();

        if ($this->user && $this->pass) {
            $request = $request->withBasicAuth($this->user, $this->pass);
        }

        return $request;
    }

    /**
     * Check if Elasticsearch is healthy and reachable
     */
    public function ping(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $response = $this->client()->get("{$this->host}/");
            return $response->successful();
        } catch (\Throwable $e) {
            Log::debug('Elasticsearch ping failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create index with custom edge_ngram analyzer and optimized mappings
     */
    public function createIndexIfNotExists(bool $recreate = false): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $check = $this->client()->head("{$this->host}/{$this->index}");

            if ($check->status() === 200) {
                if (!$recreate) {
                    return true;
                }
                // Delete existing index if recreate requested
                $this->client()->delete("{$this->host}/{$this->index}");
            }

            $schema = [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'analysis' => [
                        'filter' => [
                            'autocomplete_filter' => [
                                'type' => 'edge_ngram',
                                'min_gram' => 2,
                                'max_gram' => 20,
                            ],
                        ],
                        'analyzer' => [
                            'autocomplete' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'autocomplete_filter',
                                ],
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => [
                            'type' => 'text',
                            'analyzer' => 'autocomplete',
                            'search_analyzer' => 'standard',
                            'fields' => [
                                'keyword' => ['type' => 'keyword'],
                            ],
                        ],
                        'description' => ['type' => 'text'],
                        'short_desc' => ['type' => 'text'],
                        'price' => ['type' => 'float'],
                        'special_price' => ['type' => 'float'],
                        'category_id' => ['type' => 'integer'],
                        'category_name' => [
                            'type' => 'text',
                            'fields' => [
                                'keyword' => ['type' => 'keyword'],
                            ],
                        ],
                        'status' => ['type' => 'keyword'],
                        'stock' => ['type' => 'integer'],
                        'created_at' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis',
                        ],
                    ],
                ],
            ];

            $response = $this->client()->put("{$this->host}/{$this->index}", $schema);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Elasticsearch createIndex failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Map a Product model instance to document format
     */
    public function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => (string) $product->name,
            'description' => (string) ($product->description ?? ''),
            'short_desc' => (string) ($product->short_desc ?? ''),
            'price' => (float) ($product->price ?? 0),
            'special_price' => (float) ($product->special_price ?? 0),
            'category_id' => (int) ($product->product_category_id ?? 0),
            'category_name' => (string) ($product->category?->name ?? ''),
            'status' => (string) ($product->status?->name ?? 'published'),
            'stock' => (int) ($product->stock ?? 0),
            'created_at' => $product->created_at ? $product->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Index a single product
     */
    public function indexProduct(Product $product): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $data = $this->formatProduct($product);
            $response = $this->client()->put("{$this->host}/{$this->index}/_doc/{$product->id}", $data);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Elasticsearch indexProduct [{$product->id}] failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk index products
     */
    public function bulkIndex(iterable $products): int
    {
        if (!$this->enabled) {
            return 0;
        }

        $lines = [];
        $count = 0;

        foreach ($products as $product) {
            $lines[] = json_encode(['index' => ['_index' => $this->index, '_id' => $product->id]]);
            $lines[] = json_encode($this->formatProduct($product));
            $count++;
        }

        if (empty($lines)) {
            return 0;
        }

        $payload = implode("\n", $lines) . "\n";

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/x-ndjson'])
                ->send('POST', "{$this->host}/_bulk", ['body' => $payload]);

            if ($response->successful()) {
                $body = $response->json();
                if (!empty($body['errors'])) {
                    Log::warning('Elasticsearch bulk indexing had item errors', $body['items'] ?? []);
                }
                return $count;
            }

            Log::error('Elasticsearch bulk request failed: ' . $response->body());
            return 0;
        } catch (\Throwable $e) {
            Log::error('Elasticsearch bulk exception: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete a product document
     */
    public function deleteProduct(int|string $productId): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $response = $this->client()->delete("{$this->host}/{$this->index}/_doc/{$productId}");
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Elasticsearch deleteProduct [{$productId}] failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Full-text search with fuzzy matching, boosting, and category filtering
     */
    public function search(string $query, array $filters = [], int $limit = 20, int $page = 1): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        $from = max(0, ($page - 1) * $limit);

        $must = [];
        $filter = [];

        // Query matching with boosting and fuzziness
        if (!empty(trim($query))) {
            $must[] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => [
                        'name^4',
                        'name.keyword^5',
                        'short_desc^2',
                        'description',
                        'category_name^1.5',
                    ],
                    'fuzziness' => 'AUTO',
                    'prefix_length' => 2,
                    'operator' => 'or',
                ],
            ];
        } else {
            $must[] = ['match_all' => new \stdClass()];
        }

        // Only published products
        $filter[] = ['term' => ['status' => 'published']];

        // Optional category filter
        if (!empty($filters['category_id'])) {
            $filter[] = ['term' => ['category_id' => (int) $filters['category_id']]];
        }

        // Price range filter
        if (isset($filters['min_price']) || isset($filters['max_price'])) {
            $range = [];
            if (isset($filters['min_price'])) {
                $range['gte'] = (float) $filters['min_price'];
            }
            if (isset($filters['max_price'])) {
                $range['lte'] = (float) $filters['max_price'];
            }
            $filter[] = ['range' => ['price' => $range]];
        }

        $esQuery = [
            'from' => $from,
            'size' => $limit,
            'query' => [
                'bool' => [
                    'must' => $must,
                    'filter' => $filter,
                ],
            ],
            'highlight' => [
                'fields' => [
                    'name' => new \stdClass(),
                    'description' => new \stdClass(),
                ],
            ],
        ];

        try {
            $response = $this->client()->post("{$this->host}/{$this->index}/_search", $esQuery);

            if (!$response->successful()) {
                Log::warning('Elasticsearch search returned non-200: ' . $response->body());
                return null;
            }

            $result = $response->json();
            $hits = $result['hits']['hits'] ?? [];
            $total = $result['hits']['total']['value'] ?? count($hits);

            $ids = array_map(fn($hit) => (int) $hit['_id'], $hits);

            return [
                'ids' => $ids,
                'total' => $total,
                'hits' => $hits,
            ];
        } catch (\Throwable $e) {
            Log::debug('Elasticsearch search exception: ' . $e->getMessage());
            return null; // Signals caller to use database fallback
        }
    }
}
