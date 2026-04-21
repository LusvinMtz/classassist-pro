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
        // ─────────────────────────────────────────────
        // ROLES
        // ─────────────────────────────────────────────
        DB::table('rol')->insert([
            ['nombre' => 'admin',       'descripcion' => 'Administrador del sistema'],
            ['nombre' => 'catedratico', 'descripcion' => 'Docente'],
        ]);

        // ─────────────────────────────────────────────
        // TIPOS DE CALIFICACIÓN  (total = 100 puntos)
        // ─────────────────────────────────────────────
        DB::table('tipo_calificacion')->insert([
            ['nombre' => 'Parcial 1',    'descripcion' => 'Primer examen parcial',   'punteo_max' => 15.00, 'orden' => 1],
            ['nombre' => 'Parcial 2',    'descripcion' => 'Segundo examen parcial',  'punteo_max' => 15.00, 'orden' => 2],
            ['nombre' => 'Actividades',  'descripcion' => 'Tareas y actividades',    'punteo_max' => 25.00, 'orden' => 3],
            ['nombre' => 'Proyecto',     'descripcion' => 'Proyecto del curso',      'punteo_max' => 10.00, 'orden' => 4],
            ['nombre' => 'Examen Final', 'descripcion' => 'Examen final del curso',  'punteo_max' => 35.00, 'orden' => 5],
        ]);

        // ─────────────────────────────────────────────
        // SEDE
        // ─────────────────────────────────────────────
        DB::table('sede')->insert([
            ['nombre' => 'Sanarate', 'codigo' => 'SN', 'direccion' => 'El Progreso, Guatemala'],
        ]);

        $sanarate = DB::table('sede')->where('codigo', 'SN')->value('id');

        // ─────────────────────────────────────────────
        // CARRERA
        // ─────────────────────────────────────────────
        DB::table('carrera')->insert([
            ['nombre' => 'Ingeniería en Sistemas', 'codigo' => 'IS'],
        ]);

        $idIS = DB::table('carrera')->where('codigo', 'IS')->value('id');

        // ─────────────────────────────────────────────
        // SEDE ↔ CARRERA
        // ─────────────────────────────────────────────
        DB::table('sede_carrera')->insert([
            ['sede_id' => $sanarate, 'carrera_id' => $idIS],
        ]);

        // ─────────────────────────────────────────────
        // USUARIO ADMIN
        // ─────────────────────────────────────────────
        $admin = User::create([
            'nombre'   => 'Administrador',
            'email'    => 'lmartinezm20@miumg.edu.gt',
            'password' => Hash::make('123'),
            'estado'   => true,
        ]);

        DB::table('usuario_rol')->insert([
            'usuario_id' => $admin->id,
            'rol_id'     => 1,
        ]);

        // ─────────────────────────────────────────────
        // CLASES — IS: Ingeniería en Sistemas (10 ciclos · 50 cursos)
        // ─────────────────────────────────────────────
        $cursos = [
            // Ciclo 1
            ['nombre' => 'Contabilidad I',                              'ciclo' => 1],
            ['nombre' => 'Desarrollo Humano y Profesional',             'ciclo' => 1],
            ['nombre' => 'Introducción a los Sistemas de Cómputo',      'ciclo' => 1],
            ['nombre' => 'Lógica de Sistemas',                          'ciclo' => 1],
            ['nombre' => 'Metodología de la Investigación',             'ciclo' => 1],
            // Ciclo 2
            ['nombre' => 'Álgebra Lineal',                              'ciclo' => 2],
            ['nombre' => 'Algoritmos',                                  'ciclo' => 2],
            ['nombre' => 'Contabilidad II',                             'ciclo' => 2],
            ['nombre' => 'Matemática Discreta',                         'ciclo' => 2],
            ['nombre' => 'Precálculo',                                  'ciclo' => 2],
            // Ciclo 3
            ['nombre' => 'Cálculo I',                                   'ciclo' => 3],
            ['nombre' => 'Derecho Informático',                         'ciclo' => 3],
            ['nombre' => 'Física I',                                    'ciclo' => 3],
            ['nombre' => 'Proceso Administrativo',                      'ciclo' => 3],
            ['nombre' => 'Programación I',                              'ciclo' => 3],
            // Ciclo 4
            ['nombre' => 'Cálculo II',                                  'ciclo' => 4],
            ['nombre' => 'Estadística I',                               'ciclo' => 4],
            ['nombre' => 'Física II',                                   'ciclo' => 4],
            ['nombre' => 'Microeconomía',                               'ciclo' => 4],
            ['nombre' => 'Programación II',                             'ciclo' => 4],
            // Ciclo 5
            ['nombre' => 'Electrónica Analógica',                       'ciclo' => 5],
            ['nombre' => 'Emprendedores de Negocios',                   'ciclo' => 5],
            ['nombre' => 'Estadística II',                              'ciclo' => 5],
            ['nombre' => 'Métodos Numéricos',                           'ciclo' => 5],
            ['nombre' => 'Programación III',                            'ciclo' => 5],
            // Ciclo 6
            ['nombre' => 'Autómatas y Lenguajes Formales',              'ciclo' => 6],
            ['nombre' => 'Bases de Datos I',                            'ciclo' => 6],
            ['nombre' => 'Electrónica Digital',                         'ciclo' => 6],
            ['nombre' => 'Investigación de Operaciones',                'ciclo' => 6],
            ['nombre' => 'Sistemas Operativos I',                       'ciclo' => 6],
            // Ciclo 7
            ['nombre' => 'Análisis de Sistemas I',                      'ciclo' => 7],
            ['nombre' => 'Arquitectura de Computadoras I',              'ciclo' => 7],
            ['nombre' => 'Bases de Datos II',                           'ciclo' => 7],
            ['nombre' => 'Compiladores',                                'ciclo' => 7],
            ['nombre' => 'Sistemas Operativos II',                      'ciclo' => 7],
            // Ciclo 8
            ['nombre' => 'Análisis de Sistemas II',                     'ciclo' => 8],
            ['nombre' => 'Arquitectura de Computadoras II',             'ciclo' => 8],
            ['nombre' => 'Desarrollo Web',                              'ciclo' => 8],
            ['nombre' => 'Ética Profesional',                           'ciclo' => 8],
            ['nombre' => 'Redes de Computadoras I',                     'ciclo' => 8],
            // Ciclo 9
            ['nombre' => 'Administración de Tecnologías de Información','ciclo' => 9],
            ['nombre' => 'Ingeniería de Software',                      'ciclo' => 9],
            ['nombre' => 'Inteligencia Artificial',                     'ciclo' => 9],
            ['nombre' => 'Proyecto de Graduación I',                    'ciclo' => 9],
            ['nombre' => 'Redes de Computadoras II',                    'ciclo' => 9],
            // Ciclo 10
            ['nombre' => 'Aseguramiento de la Calidad de Software',     'ciclo' => 10],
            ['nombre' => 'Proyecto de Graduación II',                   'ciclo' => 10],
            ['nombre' => 'Seguridad y Auditoría de Sistemas',           'ciclo' => 10],
            ['nombre' => 'Seminario de Tecnologías de Información',     'ciclo' => 10],
            ['nombre' => 'Telecomunicaciones',                          'ciclo' => 10],
        ];

        foreach ($cursos as $i => $curso) {
            DB::table('clase')->insert([
                'usuario_id' => $admin->id,
                'carrera_id' => $idIS,
                'nombre'     => $curso['nombre'],
                'ciclo'      => $curso['ciclo'],
                'codigo'     => 'IS-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
            ]);
        }
    }
}
