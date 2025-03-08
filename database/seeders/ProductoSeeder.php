<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('productos')->insert([
            'name' => 'Producto 1',
            'description' => 'Descripcion del producto 1',
            'amount' => 100.00
        ]);

        DB::table('productos')->insert([
            'name' => 'Producto 2',
            'description' => 'Descripcion del producto 1',
            'amount' => 100.00
        ]);

        DB::table('productos')->insert([
            'name' => 'Producto 3',
            'description' => 'Descripcion del producto 1',
            'amount' => 100.00
        ]);
    }
}
