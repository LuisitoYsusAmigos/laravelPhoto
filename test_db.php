<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$materiales = App\Models\MaterialesVentaPersonalizada::orderBy('id', 'desc')->take(10)->get()->toArray();
print_r($materiales);
