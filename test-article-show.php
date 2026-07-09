<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/v1/articles/{{article_id}}?locale=ar', 'GET');
$response = $kernel->handle($request);
echo $response->getStatusCode().' '.substr($response->getContent(), 0, 300).PHP_EOL;
