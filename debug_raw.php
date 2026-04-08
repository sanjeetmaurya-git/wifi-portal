<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use RouterOS\Client;
use RouterOS\Query;
use RouterOS\Config;
use App\Models\Router;

$router = Router::where('active', true)->first();
$config = new Config([
    'host' => $router ? $router->ip_address : env('MIKROTIK_HOST', '192.168.88.1'),
    'user' => $router ? $router->username : env('MIKROTIK_USER', 'apiuser'),
    'pass' => $router ? $router->password : env('MIKROTIK_PASS', 'password'),
    'port' => $router ? (int) $router->port : (int) env('MIKROTIK_PORT', 8728),
]);

try {
    $client = new Client($config);
    echo "CONNECTED TO: " . $config->get('host') . "\n";
    
    $mobile = "test12345";
    $query = new Query('/ip/hotspot/user/add');
    $query->equal('name', $mobile);
    $query->equal('password', $mobile);
    $query->equal('profile', 'default');
    
    $response = $client->query($query)->read();
    echo "RAW RESPONSE: " . json_encode($response) . "\n";

} catch (Exception $e) {
    echo "CATCHED ERROR: " . $e->getMessage() . "\n";
}
