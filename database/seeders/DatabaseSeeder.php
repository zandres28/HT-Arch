<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use App\Models\WorkLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'local@ht-arch.app'],
            ['name' => 'Usuario Local', 'password' => bcrypt('local')]
        );

        // No duplicar datos demo si ya existen proyectos.
        if (Project::count() > 0) {
            return;
        }

        $crm = Project::create([
            'user_id' => $user->id,
            'name' => 'Aplicativo CRM',
            'description' => 'Desarrollo del sistema CRM para gestión de clientes.',
            'client' => 'Cliente interno',
            'start_date' => Carbon::today()->subMonths(2),
            'status' => 'active',
            'color' => '#6366f1',
        ]);

        $servidor = Project::create([
            'user_id' => $user->id,
            'name' => 'Migración de Servidor',
            'description' => 'Migración y configuración del servidor de producción.',
            'client' => 'Empresa ABC',
            'start_date' => Carbon::today()->subMonth(),
            'status' => 'active',
            'color' => '#10b981',
        ]);

        $web = Project::create([
            'user_id' => $user->id,
            'name' => 'Sitio Web Corporativo',
            'description' => 'Rediseño del sitio web corporativo.',
            'client' => 'Empresa XYZ',
            'start_date' => Carbon::today()->subMonths(3),
            'end_date' => Carbon::today()->subWeeks(2),
            'status' => 'finished',
            'color' => '#f59e0b',
        ]);

        $activities = [
            $crm->id => [
                'Desarrollo del módulo de clientes',
                'Implementación del formulario de contactos',
                'Corrección de errores en el dashboard',
                'Integración con API de correos',
                'Pruebas del módulo de reportes',
            ],
            $servidor->id => [
                'Configuración de Apache y PHP',
                'Migración de bases de datos',
                'Configuración de respaldos automáticos',
                'Hardening del servidor',
            ],
            $web->id => [
                'Diseño de la página de inicio',
                'Optimización de imágenes',
                'Ajustes finales y despliegue',
            ],
        ];

        // Generar registros de las últimas 6 semanas (lunes a viernes, con algo de variación).
        $rng = mt_rand();
        mt_srand(42); // Datos deterministas para pruebas
        for ($daysAgo = 42; $daysAgo >= 0; $daysAgo--) {
            $date = Carbon::today()->subDays($daysAgo);
            if ($date->isWeekend()) {
                continue;
            }
            if (mt_rand(1, 100) <= 20) {
                continue; // Algunos días sin actividad
            }

            // 1 o 2 registros por día
            $logsToday = mt_rand(1, 2);
            foreach (range(1, $logsToday) as $i) {
                // El proyecto web ya finalizó: no registrar horas después de su fin.
                $candidates = $date->greaterThan(Carbon::today()->subWeeks(2))
                    ? [$crm, $servidor]
                    : [$crm, $servidor, $web];

                $project = $candidates[array_rand($candidates)];
                $list = $activities[$project->id];

                WorkLog::create([
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'date' => $date,
                    'activity' => $list[array_rand($list)],
                    'description' => 'Trabajo realizado durante la jornada.',
                    'hours' => [1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 6][array_rand([1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 6])],
                ]);
            }
        }
        mt_srand($rng);
    }
}
