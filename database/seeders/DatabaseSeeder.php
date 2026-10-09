<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Sucursal;
use App\Models\Rol;
use App\Models\Cliente;
use App\Models\FormaDePago;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear sucursal genérica
        $sucursal = Sucursal::create([
            'lugar' => 'Sucursal Principal',
            'nombre_sucursal' => 'PhotoStore',
            'gerente' => 'Diego Sandoval',
            'direccion' => 'Dir: Barrio La Pampa, Av. La Paz y Potosí',
            'contactos' => '69315632 - 72993950',
            'correo' => 'fotografiatarija@gmail.com',
        ]);

        // Crear rol admin
        $rolAdmin = Rol::create([
            'nombre' => 'admin',
        ]);

        // Crear Cliente genérico
        Cliente::firstOrCreate(
            ['ci' => '0000000'],
            [
                'nombre' => 'Cliente',
                'apellido' => 'Genérico',
                'telefono' => '00000000',
                'direccion' => 'Sin dirección',
                'email' => 'cliente@generico.com'
            ]
        );

        // Crear Forma de Pago (Efectivo)
        FormaDePago::firstOrCreate(
            ['nombre' => 'Efectivo'],
            ['descripcion' => 'Pago en efectivo', 'activo' => true]
        );

        User::create([
            'name' => 'Administrador',
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password123'),
            'id_sucursal' => $sucursal->id,
            'id_rol' => $rolAdmin->id,
        ]);

        User::create([
            "name" => "David",
            "username" => "david",
            "email" => "david@gmail.com",
            "password" => bcrypt('david234567'),
            "id_sucursal" => $sucursal->id,
            "id_rol" => $rolAdmin->id,
        ]);

        // CORREGIDO: `$this->call([...]);`
        $this->call([
            ProductoSeeder::class
        ]);
    }
}
