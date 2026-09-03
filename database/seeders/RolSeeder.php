<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema'],
            ['nombre' => 'Jefe de taller', 'descripcion' => 'Gestiona ingresos, inspecciones, órdenes y asignaciones'],
            ['nombre' => 'Mecánico', 'descripcion' => 'Realiza diagnósticos, reparaciones y pruebas'],
            ['nombre' => 'Contabilidad', 'descripcion' => 'Consulta órdenes, cotizaciones e informes'],
            ['nombre' => 'Mensajero', 'descripcion' => 'Gestiona la entrega de repuestos solicitados'],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}
