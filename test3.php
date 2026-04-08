<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\MikrotikService;
use RouterOS\Query;

$m = new MikrotikService();
$client = $m->connect();
$q = new Query('/ip/hotspot/user/print');
$q->where('name', '9999999999');
file_put_contents('test_output_new.json', json_encode($client->query($q)->read(), JSON_PRETTY_PRINT));
