<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\MikrotikService;
use RouterOS\Query;

$m = new MikrotikService();
$client = $m->connect();

$userId = '*7'; // the .id for 8874599237 from earlier
$updateQuery = new Query('/ip/hotspot/user/set');
$updateQuery->equal('.id', $userId);
$updateQuery->equal('limit-bytes-total', '20971520');
$updateQuery->equal('limit-uptime', '20m');

$response = $client->query($updateQuery)->read();
file_put_contents('test_update.json', json_encode(array('response' => $response), JSON_PRETTY_PRINT));
