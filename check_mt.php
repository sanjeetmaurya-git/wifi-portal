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
    echo "CONNECTED TO: " . $config->get('host') . " ✅\n\n";
    
    echo "--- RECENT HOTSPOT LOGS ---\n";
    $query = new Query('/log/print');
    // Read last 20 logs related to hotspot
    $logs = $client->query($query)->read();
    $count = 0;
    foreach (array_reverse($logs) as $l) {
        if (strpos($l['topics'], 'hotspot') !== false) {
            echo "[" . $l['time'] . "] " . $l['message'] . "\n";
            $count++;
            if ($count > 15) break;
        }
    }

    echo "\n--- PENDING USERS (added by us recently) ---\n";
    $users = $client->query(new Query('/ip/hotspot/user/print'))->read();
    foreach ($users as $u) {
        if (strpos($u['name'], 'test') === 0 || preg_match('/^\d{10}$/', $u['name'])) {
            echo "- " . $u['name'] . " (Profile: " . ($u['profile'] ?? 'N/A') . ")\n";
        }
    }

} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
