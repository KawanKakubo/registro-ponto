<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\EmployeeRegistration;
use App\Models\Establishment;
use App\Models\WorkShiftTemplate;
use App\Models\TimeRecord;
use App\Models\AfdImport;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard
     */
    public function index()
    {
        // Estatísticas consolidadas
        $stats = $this->getConsolidatedStats();
        
        // Alertas
        $alerts = $this->getAlerts();
        
        // Dados para gráficos
        $charts = $this->getChartData();
        
        // Atividades recentes
        $recentActivity = $this->getRecentActivity();
        
        return view('dashboard', compact('stats', 'alerts', 'charts', 'recentActivity'));
    }
    
    /**
     * Get consolidated statistics
     */
    private function getConsolidatedStats(): array
    {
        return [
            // Pessoas
            'total_people' => Person::count(),
            'people_with_registrations' => Person::has('activeRegistrations')->count(),
            'people_without_registrations' => Person::doesntHave('activeRegistrations')->count(),
            
            // Vínculos
            'total_registrations' => EmployeeRegistration::count(),
            'active_registrations' => EmployeeRegistration::where('status', 'active')->count(),
            'inactive_registrations' => EmployeeRegistration::where('status', 'inactive')->count(),
            'on_leave_registrations' => EmployeeRegistration::where('status', 'on_leave')->count(),
            
            // Estabelecimentos e Departamentos
            'total_establishments' => Establishment::count(),
            'establishments_with_registrations' => Establishment::has('employeeRegistrations')->count(),
            
            // Jornadas
            'total_workshifts' => WorkShiftTemplate::count(),
            'registrations_with_workshift' => EmployeeRegistration::has('currentWorkShiftAssignment')
                ->where('status', 'active')
                ->count(),
            'registrations_without_workshift' => EmployeeRegistration::doesntHave('currentWorkShiftAssignment')
                ->where('status', 'active')
                ->count(),
            
            // Registros de Ponto
            'today_records' => TimeRecord::whereDate('recorded_at', today())->count(),
            'this_week_records' => TimeRecord::whereBetween('recorded_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month_records' => TimeRecord::whereMonth('recorded_at', now()->month)
                ->whereYear('recorded_at', now()->year)
                ->count(),
            
            // Importações
            'total_imports' => AfdImport::count(),
            'pending_imports' => AfdImport::where('status', 'pending')->count(),
            'processing_imports' => AfdImport::where('status', 'processing')->count(),
        ];
    }
    
    /**
     * Get alerts and warnings
     */
    private function getAlerts(): array
    {
        // Pessoas sem vínculos ativos
        $peopleWithoutRegistrations = Person::doesntHave('activeRegistrations')
            ->take(10)
            ->get();
        
        // Vínculos sem jornada
        $registrationsWithoutWorkshift = EmployeeRegistration::with(['person', 'establishment'])
            ->doesntHave('currentWorkShiftAssignment')
            ->where('status', 'active')
            ->take(10)
            ->get();
        
        // Importações falhadas recentemente
        $failedImports = AfdImport::where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return [
            'people_without_registrations' => [
                'count' => Person::doesntHave('activeRegistrations')->count(),
                'items' => $peopleWithoutRegistrations,
            ],
            'registrations_without_workshift' => [
                'count' => EmployeeRegistration::doesntHave('currentWorkShiftAssignment')
                    ->where('status', 'active')
                    ->count(),
                'items' => $registrationsWithoutWorkshift,
            ],
            'failed_imports' => [
                'count' => AfdImport::where('status', 'failed')->count(),
                'items' => $failedImports,
            ],
        ];
    }
    
    /**
     * Get data for charts
     */
    private function getChartData(): array
    {
        return [
            'registrations_by_establishment' => $this->getRegistrationsByEstablishment(),
            'registrations_by_status' => $this->getRegistrationsByStatus(),
            'workshift_distribution' => $this->getWorkshiftDistribution(),
            'imports_timeline' => $this->getImportsTimeline(),
        ];
    }
    
    /**
     * Vínculos por Estabelecimento
     */
    private function getRegistrationsByEstablishment()
    {
        $data = EmployeeRegistration::selectRaw('establishment_id, count(*) as total')
            ->where('status', 'active')
            ->groupBy('establishment_id')
            ->with('establishment')
            ->orderByDesc('total')
            ->take(10)
            ->get();
        
        return [
            'labels' => $data->map(fn($item) => $item->establishment->corporate_name ?? 'N/A')->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];
    }
    
    /**
     * Vínculos por Status
     */
    private function getRegistrationsByStatus()
    {
        $data = EmployeeRegistration::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();
        
        $statusLabels = [
            'active' => 'Ativos',
            'inactive' => 'Inativos',
            'on_leave' => 'Afastados',
        ];
        
        return [
            'labels' => $data->map(fn($item) => $statusLabels[$item->status] ?? $item->status)->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];
    }
    
    /**
     * Distribuição de Jornadas
     */
    private function getWorkshiftDistribution()
    {
        $data = WorkShiftTemplate::withCount('employeeRegistrations')
            ->get()
            ->filter(fn($item) => $item->employee_registrations_count > 0)
            ->sortByDesc('employee_registrations_count')
            ->take(8);
        
        return [
            'labels' => $data->pluck('name')->toArray(),
            'values' => $data->pluck('employee_registrations_count')->toArray(),
        ];
    }
    
    /**
     * Timeline de Importações AFD (últimos 30 dias)
     */
    private function getImportsTimeline()
    {
        $startDate = now()->subDays(30);
        
        $data = AfdImport::selectRaw('DATE(created_at) as date, count(*) as total')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return [
            'labels' => $data->map(fn($item) => \Carbon\Carbon::parse($item->date)->format('d/m'))->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];
    }
    
    /**
     * Get recent activity
     */
    private function getRecentActivity()
    {
        $recentImports = AfdImport::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return [
            'imports' => $recentImports,
        ];
    }
}
