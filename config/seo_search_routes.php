<?php
// This config allows SEO developers to control the search URL structure for hoardings
// Example: 'pattern' => '/billboard-advertising/{city}/{area?}'

return [
    // Main SEO-friendly search route pattern
    // 'pattern' => env('SEO_SEARCH_ROUTE_PATTERN', '/outdoor-advertising/{city}/{locality?}'),
    'pattern' => env('SEO_SEARCH_ROUTE_PATTERN', '/outdoor-advertising'),


    // You can add more patterns or options as needed
];
