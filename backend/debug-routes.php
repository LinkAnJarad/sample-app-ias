<?php
// Save this as backend/debug-routes.php and run with: php debug-routes.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== Laravel Route Diagnostics ===\n\n";

// Check if routes/api.php exists
if (file_exists('routes/api.php')) {
    echo "✅ routes/api.php exists\n";
    echo "Content preview:\n";
    echo substr(file_get_contents('routes/api.php'), 0, 500) . "...\n\n";
} else {
    echo "❌ routes/api.php NOT FOUND\n\n";
}

// Check RouteServiceProvider
$providerPath = 'app/Providers/RouteServiceProvider.php';
if (file_exists($providerPath)) {
    echo "✅ RouteServiceProvider exists\n";
    $content = file_get_contents($providerPath);
    if (strpos($content, 'routes/api.php') !== false) {
        echo "✅ API routes are being loaded\n\n";
    } else {
        echo "❌ API routes NOT being loaded in RouteServiceProvider\n\n";
    }
} else {
    echo "❌ RouteServiceProvider NOT FOUND\n\n";
}

// Check AuthController
if (file_exists('app/Http/Controllers/AuthController.php')) {
    echo "✅ AuthController exists\n\n";
} else {
    echo "❌ AuthController NOT FOUND\n\n";
}

echo "Run these commands to fix common issues:\n";
echo "php artisan route:clear\n";
echo "php artisan cache:clear\n";
echo "php artisan config:clear\n";
echo "php artisan route:list --path=api\n";