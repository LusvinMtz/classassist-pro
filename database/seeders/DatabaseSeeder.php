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
        ]);

        // Tipos de calificación (punteo_max en puntos reales, total = 100)
        DB::table('tipo_calificacion')->insert([
            ['nombre' => 'Parcial 1',    'descripcion' => 'Primer examen parcial',    'punteo_max' => 15.00, 'orden' => 1],
            ['nombre' => 'Parcial 2',    'descripcion' => 'Segundo examen parcial',   'punteo_max' => 15.00, 'orden' => 2],
            ['nombre' => 'Actividades',  'descripcion' => 'Tareas y actividades',     'punteo_max' => 25.00, 'orden' => 3],
            ['nombre' => 'Proyecto',     'descripcion' => 'Proyecto del curso',       'punteo_max' => 10.00, 'orden' => 4],
            ['nombre' => 'Examen Final', 'descripcion' => 'Examen final del curso',   'punteo_max' => 35.00, 'orden' => 5],
        ]);

        // Carreras
        $carreraId = DB::table('carrera')->insertGetId([
            'nombre' => 'Ingeniería en Sistemas',
            'codigo' => 'IS',
        ]);

        // Sedes
        $sedes = [
            ['nombre' => 'Campus Central',     'codigo' => '85', 'direccion' => 'Ciudad de Guatemala'],
            ['nombre' => 'Sede Quetzaltenango', 'codigo' => '90', 'direccion' => 'Quetzaltenango'],
            ['nombre' => 'Sede Escuintla',      'codigo' => '92', 'direccion' => 'Escuintla'],
        ];
        foreach ($sedes as $s) {
            DB::table('sede')->insert($s);
        }
        $sedeCentral = DB::table('sede')->where('codigo', '85')->value('id');

        // Asignar carrera a sede central
        DB::table('sede_carrera')->insert([
            'sede_id'    => $sedeCentral,
            'carrera_id' => $carreraId,
        ]);

        // Usuario admin
        $admin = User::create([
            'nombre'   => 'Administrador',
            'email'    => 'admin@classassist.com',
            'password' => Hash::make('123'),
            'estado'   => true,
        ]);

        DB::table('usuario_rol')->insert([
            'usuario_id' => $admin->id,
            'rol_id'     => 1,
        ]);

        // Clases de la malla curricular (código 8590-N)
        $clases = [
            // 1° Ciclo
            ['nombre' => 'Contabilidad I',                           'ciclo' => 1],
            ['nombre' => 'Desarrollo Humano y Profesional',          'ciclo' => 1],
            ['nombre' => 'Introducción a los Sistemas de Computo',   'ciclo' => 1],
            ['nombre' => 'Lógica de Sistemas',                       'ciclo' => 1],
            ['nombre' => 'Metodología de la Investigación',          'ciclo' => 1],
            // 2° Ciclo
            ['nombre' => 'Álgebra Lineal',                           'ciclo' => 2],
            ['nombre' => 'Algoritmos',                               'ciclo' => 2],
            ['nombre' => 'Contabilidad II',                          'ciclo' => 2],
            ['nombre' => 'Matemática Discreta',                      'ciclo' => 2],
            ['nombre' => 'Precálculo',                               'ciclo' => 2],
            // 3° Ciclo
            ['nombre' => 'Cálculo I',                                'ciclo' => 3],
            ['nombre' => 'Derecho Informático',                      'ciclo' => 3],
            ['nombre' => 'Física I',                                 'ciclo' => 3],
            ['nombre' => 'Proceso Administrativo',                   'ciclo' => 3],
            ['nombre' => 'Programación I',                           'ciclo' => 3],
            // 4° Ciclo
            ['nombre' => 'Cálculo II',                               'ciclo' => 4],
            ['nombre' => 'Estadística I',                            'ciclo' => 4],
            ['nombre' => 'Física II',                                'ciclo' => 4],
            ['nombre' => 'Microeconomía',                            'ciclo' => 4],
            ['nombre' => 'Programación II',                          'ciclo' => 4],
            // 5° Ciclo
            ['nombre' => 'Electrónica Analógica',                    'ciclo' => 5],
            ['nombre' => 'Emprendedores de Negocios',                'ciclo' => 5],
            ['nombre' => 'Estadística II',                           'ciclo' => 5],
            ['nombre' => 'Métodos Numéricos',                        'ciclo' => 5],
            ['nombre' => 'Programación III',                         'ciclo' => 5],
            // 6° Ciclo
            ['nombre' => 'Autómatas y Lenguajes Formales',           'ciclo' => 6],
            ['nombre' => 'Bases de Datos I',                         'ciclo' => 6],
            ['nombre' => 'Electrónica Digital',                      'ciclo' => 6],
            ['nombre' => 'Investigación de Operaciones',             'ciclo' => 6],
            ['nombre' => 'Sistemas Operativos I',                    'ciclo' => 6],
            // 7° Ciclo
            ['nombre' => 'Análisis de Sistemas I',                   'ciclo' => 7],
            ['nombre' => 'Arquitectura de Computadoras I',           'ciclo' => 7],
            ['nombre' => 'Bases de Datos II',                        'ciclo' => 7],
            ['nombre' => 'Compiladores',                             'ciclo' => 7],
            ['nombre' => 'Sistemas Operativos II',                   'ciclo' => 7],
            // 8° Ciclo
            ['nombre' => 'Análisis de Sistemas II',                  'ciclo' => 8],
            ['nombre' => 'Arquitectura de Computadoras II',          'ciclo' => 8],
            ['nombre' => 'Desarrollo Web',                           'ciclo' => 8],
            ['nombre' => 'Ética Profesional',                        'ciclo' => 8],
            ['nombre' => 'Redes de Computadoras I',                  'ciclo' => 8],
            // 9° Ciclo
            ['nombre' => 'Administración de Tecnologías de Información', 'ciclo' => 9],
            ['nombre' => 'Ingeniería de Software',                   'ciclo' => 9],
            ['nombre' => 'Inteligencia Artificial',                  'ciclo' => 9],
            ['nombre' => 'Proyecto de Graduación I',                 'ciclo' => 9],
            ['nombre' => 'Redes de Computadoras II',                 'ciclo' => 9],
            // 10° Ciclo
            ['nombre' => 'Aseguramiento de la Calidad de Software',  'ciclo' => 10],
            ['nombre' => 'Proyecto de Graduación II',                'ciclo' => 10],
            ['nombre' => 'Seguridad y Auditoría de Sistemas',        'ciclo' => 10],
            ['nombre' => 'Seminario de Tecnologías de Información',  'ciclo' => 10],
            ['nombre' => 'Telecomunicaciones',                       'ciclo' => 10],
        ];

        foreach ($clases as $i => $clase) {
            DB::table('clase')->insert([
                'usuario_id' => $admin->id,
                'carrera_id' => $carreraId,
                'nombre'     => $clase['nombre'],
                'ciclo'      => $clase['ciclo'],
                'codigo'     => '8590-' . ($i + 1),
            ]);
        }
    }
}
