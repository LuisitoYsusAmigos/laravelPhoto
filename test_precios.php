<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cuadros = [
    [
      "lado_a" => 10,
      "lado_b" => 10, 
      "id_materia_prima_varillas" => 9,   
      "id_materia_prima_vidrios" => 6,
      "id_materia_prima_contornos" => 1,
      "cantidad" => 1 
    ]
];

$mController = new \App\Http\Controllers\GestionVentas\GestionMarcosController();
$marcoExterno = $mController->obtenerMarcoExterno($cuadros);

echo "Lados externos:\n";
print_r($marcoExterno);

$cuadros[0]['lado_a'] = $marcoExterno['lado_a'];
$cuadros[0]['lado_b'] = $marcoExterno['lado_b'];

echo "-------------------------------------\n";
echo "Prueba Varillas:\n";
$pv = $mController->simularVarillas($cuadros[0]);
echo "Precio simulado Varillas: " . $pv . "\n";

echo "-------------------------------------\n";
echo "Prueba Vidrios:\n";
$pvi = $mController->simularVidrios($cuadros[0]);
echo "Precio simulado Vidrios: " . $pvi . "\n";

echo "-------------------------------------\n";
echo "Prueba Contornos:\n";
$pc = $mController->simularContornos($cuadros[0]);
echo "Precio simulado Contornos: " . $pc . "\n";

