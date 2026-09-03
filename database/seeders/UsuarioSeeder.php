<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin = Rol::where('nombre', 'Administrador')->first();

        Usuario::firstOrCreate(
            ['email' => 'admin@gestiondiesel.com'],
            [
                'nombre' => 'Admin',
                'apellido' => 'Principal',
                'password' => Hash::make('password123'),
                'rol_id' => $rolAdmin->id,
                'activo' => true,
            ]
        );
    }
}