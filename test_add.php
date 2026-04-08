<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\MikrotikService;

$m = new MikrotikService();
$mobile = "test999";
echo "Attempting to add user $mobile...\n";
try {
    $m->addHotspotUser($mobile, $mobile, 'default');
    echo "SUCCESS ✅\n";
} catch (Exception $e) {
    echo "FAILED ❌  $e\n";
}
