<?php declare(strict_types = 1);

define('LARAVEL_START', microtime(true));

$app = require_once __DIR__ . '/../../../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
