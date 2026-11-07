<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$dbProjects = \Illuminate\Support\Facades\DB::table('projects')->get();
foreach ($dbProjects as $p) {
    echo "ID: {$p->id}, Name: {$p->name}, Code: {$p->code}\n";
}
