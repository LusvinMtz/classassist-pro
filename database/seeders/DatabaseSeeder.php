<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        DB::table('rol')->insert([
            ['nombre' => 'admin',       'descripcion' => 'Administrador del sistema'],
            ['nombre' => 'catedratico', 'descripcion' => 'Docente'],
            ['nombre' => 'estudiante',  'descripcion' => 'Alumno'],
        ]);

        // Tipos de calificación (punteo_max en puntos reales, total = 100)
        DB::table('tipo_calificacion')->insert([
            ['nombre' => 'Parcial 1',    'descripcion' => 'Primer examen parcial',    'punteo_max' => 15.00, 'orden' => 1],
            ['nombre' => 'Parcial 2',    'descripcion' => 'Segundo examen parcial',   'punteo_max' => 15.00, 'orden' => 2],
            ['nombre' => 'Actividades',  'descripcion' => 'Tareas y actividades',     'punteo_max' => 25.00, 'orden' => 3],
            ['nombre' => 'Proyecto',     'descripcion' => 'Proyecto del curso',       'punteo_max' => 10.00, 'orden' => 4],
            ['nombre' => 'Examen Final', 'descripcion' => 'Examen final del curso',   'punteo_max' => 35.00, 'orden' => 5],
        ]);

        // Usuario admin
        $admin = User::create([
            'nombre'   => 'Administrador',
            'email'    => 'admin@classassist.com',
            'password' => Hash::make('123'),
            'estado'   => true,
        ]);

        // Asignar rol admin
        DB::table('usuario_rol')->insert([
            'usuario_id' => $admin->id,
            'rol_id'     => 1, // admin
        ]);
    }
}
