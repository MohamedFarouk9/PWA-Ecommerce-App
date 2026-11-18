<?php

return [
    'enabled' => env('ELASTICSEARCH_ENABLED', true),
    'host' => env('ELASTICSEARCH_HOST', 'http://127.0.0.1:9200'),
    'index' => env('ELASTICSEARCH_INDEX', 'ecommerce_products'),
    'user' => env('ELASTICSEARCH_USER', null),
    'pass' => env('ELASTICSEARCH_PASS', null),
    'timeout' => (float) env('ELASTICSEARCH_TIMEOUT', 2.0), // 2 seconds fast timeout
];
