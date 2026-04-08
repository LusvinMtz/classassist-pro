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
        // SEDES
        // ─────────────────────────────────────────────
        DB::table('sede')->insert([
            ['nombre' => 'Guastatoya', 'codigo' => 'GT', 'direccion' => 'El Progreso, Guatemala'],
            ['nombre' => 'Sanarate',   'codigo' => 'SN', 'direccion' => 'El Progreso, Guatemala'],
        ]);

        $guastatoya = DB::table('sede')->where('codigo', 'GT')->value('id');
        $sanarate   = DB::table('sede')->where('codigo', 'SN')->value('id');

        // ─────────────────────────────────────────────
        // CARRERAS
        // ─────────────────────────────────────────────
        $carreras = [
            ['nombre' => 'Ingeniería en Sistemas',         'codigo' => 'IS'],
            ['nombre' => 'Administración de Empresas',     'codigo' => 'AE'],
            ['nombre' => 'Ciencias Jurídicas y Sociales',  'codigo' => 'CJS'],
            ['nombre' => 'Contaduría Pública y Auditoría', 'codigo' => 'CPA'],
            ['nombre' => 'Psicología',                     'codigo' => 'PSI'],
            ['nombre' => 'Trabajo Social',                 'codigo' => 'TS'],
        ];

        foreach ($carreras as $carrera) {
            DB::table('carrera')->insert($carrera);
        }

        $idIS  = DB::table('carrera')->where('codigo', 'IS')->value('id');
        $idAE  = DB::table('carrera')->where('codigo', 'AE')->value('id');
        $idCJS = DB::table('carrera')->where('codigo', 'CJS')->value('id');
        $idCPA = DB::table('carrera')->where('codigo', 'CPA')->value('id');
        $idPSI = DB::table('carrera')->where('codigo', 'PSI')->value('id');
        $idTS  = DB::table('carrera')->where('codigo', 'TS')->value('id');

        // ─────────────────────────────────────────────
        // SEDE ↔ CARRERA
        //   Guastatoya: todas las carreras
        //   Sanarate:   IS, AE, CJS
        // ─────────────────────────────────────────────
        $sedeCarrera = [
            // Guastatoya — 6 carreras
            ['sede_id' => $guastatoya, 'carrera_id' => $idIS],
            ['sede_id' => $guastatoya, 'carrera_id' => $idAE],
            ['sede_id' => $guastatoya, 'carrera_id' => $idCJS],
            ['sede_id' => $guastatoya, 'carrera_id' => $idCPA],
            ['sede_id' => $guastatoya, 'carrera_id' => $idPSI],
            ['sede_id' => $guastatoya, 'carrera_id' => $idTS],
            // Sanarate — 3 carreras
            ['sede_id' => $sanarate, 'carrera_id' => $idIS],
            ['sede_id' => $sanarate, 'carrera_id' => $idAE],
            ['sede_id' => $sanarate, 'carrera_id' => $idCJS],
        ];

        DB::table('sede_carrera')->insert($sedeCarrera);

        // ─────────────────────────────────────────────
        // USUARIO ADMIN
        // ─────────────────────────────────────────────
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

        // ─────────────────────────────────────────────
        // CLASES — helper para insertar en lote
        // ─────────────────────────────────────────────
        $insertClases = function (array $cursos, int $carreraId, string $prefijo) use ($admin) {
            foreach ($cursos as $i => $curso) {
                DB::table('clase')->insert([
                    'usuario_id' => $admin->id,
                    'carrera_id' => $carreraId,
                    'nombre'     => $curso['nombre'],
                    'ciclo'      => $curso['ciclo'],
                    'codigo'     => $prefijo . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                ]);
            }
        };

        // ── IS: Ingeniería en Sistemas (10 ciclos · 50 cursos) ──────────────
        $insertClases([
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
        ], $idIS, 'IS');

        // ── AE: Administración de Empresas (8 ciclos · 40 cursos) ──────────
        $insertClases([
            // Ciclo 1
            ['nombre' => 'Administración I',                            'ciclo' => 1],
            ['nombre' => 'Derecho I',                                   'ciclo' => 1],
            ['nombre' => 'Desarrollo Humano y Profesional',             'ciclo' => 1],
            ['nombre' => 'Filosofía Administrativa',                    'ciclo' => 1],
            ['nombre' => 'Lenguaje e Investigación Documental',         'ciclo' => 1],
            // Ciclo 2
            ['nombre' => 'Administración II',                           'ciclo' => 2],
            ['nombre' => 'Contabilidad I',                              'ciclo' => 2],
            ['nombre' => 'Derecho II',                                  'ciclo' => 2],
            ['nombre' => 'Economía General',                            'ciclo' => 2],
            ['nombre' => 'Matemática I',                                'ciclo' => 2],
            // Ciclo 3
            ['nombre' => 'Administración III',                          'ciclo' => 3],
            ['nombre' => 'Contabilidad II',                             'ciclo' => 3],
            ['nombre' => 'Matemática Administrativa',                   'ciclo' => 3],
            ['nombre' => 'Microeconomía',                               'ciclo' => 3],
            ['nombre' => 'Psicología Organizacional',                   'ciclo' => 3],
            // Ciclo 4
            ['nombre' => 'Administración IV',                           'ciclo' => 4],
            ['nombre' => 'Estadística I',                               'ciclo' => 4],
            ['nombre' => 'Informática I',                               'ciclo' => 4],
            ['nombre' => 'Macroeconomía',                               'ciclo' => 4],
            ['nombre' => 'Sociología de los Negocios',                  'ciclo' => 4],
            // Ciclo 5
            ['nombre' => 'Administración de Costos',                    'ciclo' => 5],
            ['nombre' => 'Administración del Talento Humano',           'ciclo' => 5],
            ['nombre' => 'Estadística II',                              'ciclo' => 5],
            ['nombre' => 'Informática II',                              'ciclo' => 5],
            ['nombre' => 'Mercadotecnia I',                             'ciclo' => 5],
            // Ciclo 6
            ['nombre' => 'Derecho III',                                 'ciclo' => 6],
            ['nombre' => 'Economía Internacional',                      'ciclo' => 6],
            ['nombre' => 'Gestión de Estados Financieros',              'ciclo' => 6],
            ['nombre' => 'Investigación de Operaciones',                'ciclo' => 6],
            ['nombre' => 'Mercadotecnia II',                            'ciclo' => 6],
            // Ciclo 7
            ['nombre' => 'Administración de Presupuestos',              'ciclo' => 7],
            ['nombre' => 'Administración Industrial',                   'ciclo' => 7],
            ['nombre' => 'Matemática Financiera',                       'ciclo' => 7],
            ['nombre' => 'Mercadotecnia III',                           'ciclo' => 7],
            ['nombre' => 'Proyecto de Graduación',                      'ciclo' => 7],
            // Ciclo 8
            ['nombre' => 'Administración Financiera',                   'ciclo' => 8],
            ['nombre' => 'Elaboración y Evaluación de Proyectos',       'ciclo' => 8],
            ['nombre' => 'Logística Administrativa',                    'ciclo' => 8],
            ['nombre' => 'Mercadotecnia IV',                            'ciclo' => 8],
            ['nombre' => 'Seminario de Competencias Gerenciales',       'ciclo' => 8],
        ], $idAE, 'AE');

        // ── CJS: Ciencias Jurídicas y Sociales (10 ciclos · 55 cursos) ─────
        $insertClases([
            // Ciclo 1
            ['nombre' => 'Economía',                                    'ciclo' => 1],
            ['nombre' => 'Filosofía',                                   'ciclo' => 1],
            ['nombre' => 'Introducción al Derecho I',                   'ciclo' => 1],
            ['nombre' => 'Lenguaje y Técnicas de Investigación',        'ciclo' => 1],
            ['nombre' => 'Sociología de Guatemala',                     'ciclo' => 1],
            // Ciclo 2
            ['nombre' => 'Criminología',                                'ciclo' => 2],
            ['nombre' => 'Derecho Penal I',                             'ciclo' => 2],
            ['nombre' => 'Derecho Romano y Español',                    'ciclo' => 2],
            ['nombre' => 'Desarrollo Humano y Profesional',             'ciclo' => 2],
            ['nombre' => 'Introducción al Derecho II',                  'ciclo' => 2],
            ['nombre' => 'Teoría General del Estado',                   'ciclo' => 2],
            // Ciclo 3
            ['nombre' => 'Derecho Civil I',                             'ciclo' => 3],
            ['nombre' => 'Derecho Constitucional Guatemalteco',         'ciclo' => 3],
            ['nombre' => 'Derecho Penal II',                            'ciclo' => 3],
            ['nombre' => 'Medicina Forense',                            'ciclo' => 3],
            ['nombre' => 'Teoría General del Proceso',                  'ciclo' => 3],
            // Ciclo 4
            ['nombre' => 'Derecho Administrativo I',                    'ciclo' => 4],
            ['nombre' => 'Derecho Ambiental',                           'ciclo' => 4],
            ['nombre' => 'Derecho Civil II',                            'ciclo' => 4],
            ['nombre' => 'Derecho Penal III',                           'ciclo' => 4],
            ['nombre' => 'Derechos Humanos',                            'ciclo' => 4],
            ['nombre' => 'Lógica Jurídica y Ética Profesional',         'ciclo' => 4],
            // Ciclo 5
            ['nombre' => 'Derecho Administrativo II',                   'ciclo' => 5],
            ['nombre' => 'Derecho Civil III',                           'ciclo' => 5],
            ['nombre' => 'Derecho del Trabajo I',                       'ciclo' => 5],
            ['nombre' => 'Derecho Financiero y Tributario',             'ciclo' => 5],
            ['nombre' => 'Derecho Procesal Penal I',                    'ciclo' => 5],
            ['nombre' => 'Oratoria Forense',                            'ciclo' => 5],
            // Ciclo 6
            ['nombre' => 'Derecho Civil IV',                            'ciclo' => 6],
            ['nombre' => 'Derecho del Trabajo II',                      'ciclo' => 6],
            ['nombre' => 'Derecho Mercantil I',                         'ciclo' => 6],
            ['nombre' => 'Derecho Procesal Administrativo',             'ciclo' => 6],
            ['nombre' => 'Derecho Procesal Penal II',                   'ciclo' => 6],
            // Ciclo 7
            ['nombre' => 'Clínica Procesal Penal I',                    'ciclo' => 7],
            ['nombre' => 'Derecho Civil V',                             'ciclo' => 7],
            ['nombre' => 'Derecho Mercantil II',                        'ciclo' => 7],
            ['nombre' => 'Derecho Notarial I',                          'ciclo' => 7],
            ['nombre' => 'Derecho Procesal del Trabajo',                'ciclo' => 7],
            ['nombre' => 'Seminario de Trabajo de Graduación',          'ciclo' => 7],
            // Ciclo 8
            ['nombre' => 'Clínica Procesal Laboral',                    'ciclo' => 8],
            ['nombre' => 'Clínica Procesal Penal II',                   'ciclo' => 8],
            ['nombre' => 'Derecho Internacional Público',               'ciclo' => 8],
            ['nombre' => 'Derecho Mercantil III',                       'ciclo' => 8],
            ['nombre' => 'Derecho Notarial II',                         'ciclo' => 8],
            ['nombre' => 'Derecho Procesal Civil y Mercantil I',        'ciclo' => 8],
            // Ciclo 9
            ['nombre' => 'Clínica Procesal Civil I',                    'ciclo' => 9],
            ['nombre' => 'Derecho Notarial III',                        'ciclo' => 9],
            ['nombre' => 'Derecho Procesal Civil y Mercantil II',       'ciclo' => 9],
            ['nombre' => 'Derecho Procesal Constitucional',             'ciclo' => 9],
            ['nombre' => 'Derecho Registral',                           'ciclo' => 9],
            // Ciclo 10
            ['nombre' => 'Clínica Procesal Civil II',                   'ciclo' => 10],
            ['nombre' => 'Derecho Bancario y Bursátil',                 'ciclo' => 10],
            ['nombre' => 'Derecho Internacional Privado',               'ciclo' => 10],
            ['nombre' => 'Derecho Procesal Civil y Mercantil III',      'ciclo' => 10],
            ['nombre' => 'Filosofía del Derecho',                       'ciclo' => 10],
        ], $idCJS, 'CJS');

        // ── CPA: Contaduría Pública y Auditoría (9 ciclos · 45 cursos) ─────
        $insertClases([
            // Ciclo 1
            ['nombre' => 'Contabilidad Básica',                         'ciclo' => 1],
            ['nombre' => 'Introducción a la Economía',                  'ciclo' => 1],
            ['nombre' => 'Introducción al Derecho',                     'ciclo' => 1],
            ['nombre' => 'Matemática I',                                'ciclo' => 1],
            ['nombre' => 'Técnicas de Investigación',                   'ciclo' => 1],
            // Ciclo 2
            ['nombre' => 'Contabilidad de Sociedades',                  'ciclo' => 2],
            ['nombre' => 'Legislación Mercantil',                       'ciclo' => 2],
            ['nombre' => 'Matemática II',                               'ciclo' => 2],
            ['nombre' => 'Microeconomía I',                             'ciclo' => 2],
            ['nombre' => 'Normas Internacionales de Información Financiera I',  'ciclo' => 2],
            // Ciclo 3
            ['nombre' => 'Contabilidad Avanzada I',                     'ciclo' => 3],
            ['nombre' => 'Matemática Financiera I',                     'ciclo' => 3],
            ['nombre' => 'Métodos Estadísticos I',                      'ciclo' => 3],
            ['nombre' => 'Microeconomía II',                            'ciclo' => 3],
            ['nombre' => 'Normas Internacionales de Información Financiera II', 'ciclo' => 3],
            // Ciclo 4
            ['nombre' => 'Contabilidad Avanzada II',                    'ciclo' => 4],
            ['nombre' => 'Desarrollo Humano y Profesional',             'ciclo' => 4],
            ['nombre' => 'Matemática Financiera II',                    'ciclo' => 4],
            ['nombre' => 'Métodos Estadísticos II',                     'ciclo' => 4],
            ['nombre' => 'Teoría Administrativa',                       'ciclo' => 4],
            // Ciclo 5
            ['nombre' => 'Auditoría I',                                 'ciclo' => 5],
            ['nombre' => 'Contabilidad de Costos I',                    'ciclo' => 5],
            ['nombre' => 'Finanzas Públicas',                           'ciclo' => 5],
            ['nombre' => 'Legislación Tributaria',                      'ciclo' => 5],
            ['nombre' => 'Normas Internacionales de Auditoría I',       'ciclo' => 5],
            // Ciclo 6
            ['nombre' => 'Auditoría II',                                'ciclo' => 6],
            ['nombre' => 'Contabilidad de Costos II',                   'ciclo' => 6],
            ['nombre' => 'Legislación Laboral',                         'ciclo' => 6],
            ['nombre' => 'Moneda y Banca',                              'ciclo' => 6],
            ['nombre' => 'Normas Internacionales de Auditoría II',      'ciclo' => 6],
            // Ciclo 7
            ['nombre' => 'Análisis e Interpretación de Estados Financieros', 'ciclo' => 7],
            ['nombre' => 'Auditoría III',                               'ciclo' => 7],
            ['nombre' => 'Ética Profesional',                           'ciclo' => 7],
            ['nombre' => 'Presupuestos',                                'ciclo' => 7],
            ['nombre' => 'Procedimientos Legales y Administrativos',    'ciclo' => 7],
            // Ciclo 8
            ['nombre' => 'Auditoría Administrativa',                    'ciclo' => 8],
            ['nombre' => 'Auditoría de Sistemas de Información',        'ciclo' => 8],
            ['nombre' => 'Contabilidad y Organización Bancaria',        'ciclo' => 8],
            ['nombre' => 'Contabilidades Especiales',                   'ciclo' => 8],
            ['nombre' => 'Redacción de Informes Técnicos',              'ciclo' => 8],
            // Ciclo 9
            ['nombre' => 'Administración y Gestión de Riesgos',         'ciclo' => 9],
            ['nombre' => 'Elaboración y Evaluación de Proyectos',       'ciclo' => 9],
            ['nombre' => 'Propedéutica de Tesis',                       'ciclo' => 9],
            ['nombre' => 'Seminario de Auditoría',                      'ciclo' => 9],
            ['nombre' => 'Seminario de Contabilidad',                   'ciclo' => 9],
        ], $idCPA, 'CPA');

        // ── PSI: Psicología (10 ciclos · 44 cursos) ─────────────────────────
        $insertClases([
            // Ciclo 1
            ['nombre' => 'Biología Humana',                             'ciclo' => 1],
            ['nombre' => 'Desarrollo Humano y Profesional',             'ciclo' => 1],
            ['nombre' => 'Filosofía',                                   'ciclo' => 1],
            ['nombre' => 'Sociología General',                          'ciclo' => 1],
            // Ciclo 2
            ['nombre' => 'Antropología General',                        'ciclo' => 2],
            ['nombre' => 'Lógica Formal',                               'ciclo' => 2],
            ['nombre' => 'Metodología de la Investigación',             'ciclo' => 2],
            ['nombre' => 'Psicología General',                          'ciclo' => 2],
            // Ciclo 3
            ['nombre' => 'Anatomía y Fisiología del Sistema Nervioso',  'ciclo' => 3],
            ['nombre' => 'Estadística Fundamental',                     'ciclo' => 3],
            ['nombre' => 'Psicología Evolutiva del Niño y del Adolescente', 'ciclo' => 3],
            ['nombre' => 'Semiología Psicológica',                      'ciclo' => 3],
            // Ciclo 4
            ['nombre' => 'Estadística Aplicada a la Psicología',        'ciclo' => 4],
            ['nombre' => 'Psicología Evolutiva del Adulto',             'ciclo' => 4],
            ['nombre' => 'Psicometría I',                               'ciclo' => 4],
            ['nombre' => 'Teorías de la Personalidad',                  'ciclo' => 4],
            // Ciclo 5
            ['nombre' => 'Neurofisiología',                             'ciclo' => 5],
            ['nombre' => 'Psicología del Deporte y la Recreación',      'ciclo' => 5],
            ['nombre' => 'Psicología Social',                           'ciclo' => 5],
            ['nombre' => 'Psicometría II',                              'ciclo' => 5],
            // Ciclo 6
            ['nombre' => 'Fundamentos de Informática',                  'ciclo' => 6],
            ['nombre' => 'Introducción a la Psicología Forense',        'ciclo' => 6],
            ['nombre' => 'Introducción a la Psicología Industrial Organizacional', 'ciclo' => 6],
            ['nombre' => 'Psicología Clínica',                          'ciclo' => 6],
            // Ciclo 7
            ['nombre' => 'Fundamentos Teóricos de la Terapia Analítica','ciclo' => 7],
            ['nombre' => 'Psiconeuroendocrinología',                    'ciclo' => 7],
            ['nombre' => 'Psicopatología del Adulto I',                 'ciclo' => 7],
            ['nombre' => 'Psicopatología del Niño y del Adolescente',   'ciclo' => 7],
            ['nombre' => 'Sistemas de Psicoterapia',                    'ciclo' => 7],
            // Ciclo 8
            ['nombre' => 'Proceso Terapéutico Analítico',               'ciclo' => 8],
            ['nombre' => 'Psicometría III',                             'ciclo' => 8],
            ['nombre' => 'Psicopatología del Adulto II',                'ciclo' => 8],
            ['nombre' => 'Psicoterapia del Niño y del Adolescente',     'ciclo' => 8],
            ['nombre' => 'Técnicas de Modificación de Conductas',       'ciclo' => 8],
            // Ciclo 9
            ['nombre' => 'Elaboración de Trabajo de Graduación I',      'ciclo' => 9],
            ['nombre' => 'Fundamentos de Psicofarmacología',            'ciclo' => 9],
            ['nombre' => 'Modelo Psicoterapéutico Cognitivo Conductual','ciclo' => 9],
            ['nombre' => 'Práctica I',                                  'ciclo' => 9],
            ['nombre' => 'Psicoeducación en Trastornos Mentales',       'ciclo' => 9],
            // Ciclo 10
            ['nombre' => 'Elaboración de Trabajo de Graduación II',     'ciclo' => 10],
            ['nombre' => 'Modelo Psicoterapéutico Humanístico Existencial', 'ciclo' => 10],
            ['nombre' => 'Práctica II',                                 'ciclo' => 10],
            ['nombre' => 'Psicoterapia de Grupo',                       'ciclo' => 10],
            ['nombre' => 'Psicoterapia de Pareja Familiar',             'ciclo' => 10],
        ], $idPSI, 'PSI');

        // ── TS: Trabajo Social (10 ciclos · 40 cursos) ──────────────────────
        $insertClases([
            // Ciclo 1
            ['nombre' => 'Filosofía General',                                       'ciclo' => 1],
            ['nombre' => 'Herramientas de la Comunicación Social',                  'ciclo' => 1],
            ['nombre' => 'Historia Política y Social de Guatemala',                 'ciclo' => 1],
            ['nombre' => 'Origen y Desarrollo de Trabajo Social',                   'ciclo' => 1],
            // Ciclo 2
            ['nombre' => 'Estudio de la Realidad Social de Guatemala',              'ciclo' => 2],
            ['nombre' => 'Introducción a las Ciencias Sociales',                    'ciclo' => 2],
            ['nombre' => 'Lógica',                                                  'ciclo' => 2],
            ['nombre' => 'Psicología Social',                                       'ciclo' => 2],
            // Ciclo 3
            ['nombre' => 'Estadística Aplicada al Trabajo Social',                  'ciclo' => 3],
            ['nombre' => 'Investigación Científica Enfocada a las Ciencias Sociales I',  'ciclo' => 3],
            ['nombre' => 'Metodología para la Intervención Individual y Familiar',  'ciclo' => 3],
            ['nombre' => 'Teoría del Estado Moderno',                               'ciclo' => 3],
            // Ciclo 4
            ['nombre' => 'Desarrollo Humano y Profesional',                         'ciclo' => 4],
            ['nombre' => 'Investigación Científica Enfocada a las Ciencias Sociales II', 'ciclo' => 4],
            ['nombre' => 'Legislación Social y Derechos Humanos I',                 'ciclo' => 4],
            ['nombre' => 'Metodología para la Intervención Grupal',                 'ciclo' => 4],
            // Ciclo 5
            ['nombre' => 'Ejercicio Técnico Supervisado I',                         'ciclo' => 5],
            ['nombre' => 'Formulación de Proyectos',                                'ciclo' => 5],
            ['nombre' => 'Legislación Social y Derechos Humanos II',                'ciclo' => 5],
            ['nombre' => 'Metodología para la Intervención Comunitaria',            'ciclo' => 5],
            // Ciclo 6
            ['nombre' => 'Economía Política',                                       'ciclo' => 6],
            ['nombre' => 'Ejercicio Técnico Supervisado II',                        'ciclo' => 6],
            ['nombre' => 'Interculturalidad de Guatemala',                          'ciclo' => 6],
            ['nombre' => 'Seminario de Trabajo Social',                             'ciclo' => 6],
            // Ciclo 7
            ['nombre' => 'Gerencia Social I',                                       'ciclo' => 7],
            ['nombre' => 'Investigación y Trabajo Social',                           'ciclo' => 7],
            ['nombre' => 'Políticas Públicas y Sociales',                           'ciclo' => 7],
            ['nombre' => 'Principios Filosóficos del Trabajo Social',               'ciclo' => 7],
            // Ciclo 8
            ['nombre' => 'Campos de Intervención de Trabajo Social',                'ciclo' => 8],
            ['nombre' => 'Evaluación y Monitoreo de Proyectos',                     'ciclo' => 8],
            ['nombre' => 'Gerencia Social II',                                      'ciclo' => 8],
            ['nombre' => 'Taller: Sistematización de Experiencias de Trabajo Social','ciclo' => 8],
            // Ciclo 9
            ['nombre' => 'Antropología Social',                                     'ciclo' => 9],
            ['nombre' => 'Planificación del Desarrollo Social I',                   'ciclo' => 9],
            ['nombre' => 'Práctica Profesional I',                                  'ciclo' => 9],
            ['nombre' => 'Trabajo de Graduación I',                                 'ciclo' => 9],
            // Ciclo 10
            ['nombre' => 'Auditoría Social',                                        'ciclo' => 10],
            ['nombre' => 'Evaluación y Resolución de Conflictos',                   'ciclo' => 10],
            ['nombre' => 'Planificación del Desarrollo Social II',                  'ciclo' => 10],
            ['nombre' => 'Trabajo de Graduación II',                                'ciclo' => 10],
        ], $idTS, 'TS');
    }
}
