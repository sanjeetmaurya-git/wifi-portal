<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\MikrotikService;

$m = new MikrotikService();
$result = $m->addHotspotUser('9999999999', '9999999999', 'default', '20m', '20971520', null);

echo "Success: " . ($result ? "Yes" : "No") . "\n";
