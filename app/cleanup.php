<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$tenants = Tenant::all();
echo "Tenants:\n";
foreach($tenants as $t) {
    $userCount = User::withoutGlobalScopes()->where('tenant_id', $t->id)->count();
    echo "ID: {$t->id} | Name: {$t->name} | Users: {$userCount}\n";
    
    // Cleanup empty tenants created during the failed OAuth attempt (assuming tenant ID > 1 and 0 users)
    if ($t->id > 2 && $userCount === 0) {
        echo " -> Deleting orphaned tenant {$t->id}\n";
        $t->delete();
    }
}
echo "Cleanup complete.\n";
