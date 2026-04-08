<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WifiUser;
use App\Models\WifiSession;
use App\Services\MikrotikService;

$mobile = '7004579654';

echo "--- CLEANING UP $mobile ---\n";

// 1. Delete DB Sessions
$deletedSessions = WifiSession::whereHas('user', function($q) use ($mobile) {
    $q->where('mobile', $mobile);
})->delete();
echo "Deleted DB Sessions: $deletedSessions\n";

// 2. Delete DB User
$deletedUsers = WifiUser::where('mobile', $mobile)->delete();
echo "Deleted DB User: $deletedUsers\n";

// 3. Delete from MikroTik
try {
    $mt = new MikrotikService();
    $mt->removeActiveSession($mobile);
    
    // Also delete user from hotspot user list
    $client = (new ReflectionObject($mt))->getMethod('getClient')->getClosure($mt)();
    $query = new RouterOS\Query('/ip/hotspot/user/print');
    $query->where('name', $mobile);
    $users = $client->query($query)->read();
    
    foreach ($users as $u) {
        $remove = new RouterOS\Query('/ip/hotspot/user/remove');
        $remove->equal('.id', $u['.id']);
        $client->query($remove)->read();
        echo "Removed from MikroTik User List: " . $mobile . "\n";
    }
    
} catch (Exception $e) {
    echo "MikroTik Cleanup Error: " . $e->getMessage() . "\n";
}

echo "--- CLEANUP COMPLETE ---\n";
