<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Add your API or web paths here

    'allowed_methods' => ['*'], // Allow all methods (GET, POST, PUT, DELETE, etc.)

    'allowed_origins' => ['*'], // You can replace '*' with specific domains if needed

    'allowed_origins_patterns' => [], // Patterns for matching origins

    'allowed_headers' => ['*'], // Allow all headers

    'exposed_headers' => [], // Headers to expose to the browser

    'max_age' => 0, // Cache the CORS response for this duration

    'supports_credentials' => true, // Set to true if your requests include credentials (cookies, etc.)

];
