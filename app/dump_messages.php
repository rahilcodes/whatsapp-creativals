<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$messages = \App\Models\Message::withoutGlobalScope('tenant')
    ->where('phone', '191555608510539')
    ->orderBy('id')
    ->get(['id', 'role', 'content', 'created_at', 'tenant_id']);

foreach ($messages as $m) {
    echo "ID: {$m->id} | Role: {$m->role} | Created: {$m->created_at->toIso8601String()} | Content: " . substr(trim($m->content), 0, 40) . "\n";
}
