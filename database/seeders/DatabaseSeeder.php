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

        // Tipos de calificación
        DB::table('tipo_calificacion')->insert([
            ['nombre' => 'asistencia',    'descripcion' => 'Puntos por asistencia'],
            ['nombre' => 'participacion', 'descripcion' => 'Puntos por participación'],
            ['nombre' => 'grupo',         'descripcion' => 'Puntos por trabajo en grupo'],
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
