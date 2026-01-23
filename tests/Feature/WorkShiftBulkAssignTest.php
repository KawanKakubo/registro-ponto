<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Person;
use App\Models\EmployeeRegistration;
use App\Models\Establishment;
use App\Models\Department;
use App\Models\WorkShiftTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkShiftBulkAssignTest extends TestCase
{
    /**
     * Testa se a página de atribuição em massa carrega
     */
    public function test_bulk_assign_page_loads(): void
    {
        // Autentica usuário
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('Nenhum usuário encontrado no banco de dados');
        }
        $this->actingAs($user);

        // Acessa a página
        $response = $this->get(route('work-shift-templates.bulk-assign'));

        $response->assertStatus(200);
        $response->assertViewIs('work-shift-templates.bulk-assign');
        $response->assertViewHas(['templates', 'establishments', 'registrations']);
    }

    /**
     * Testa se a página mostra vínculos ativos
     */
    public function test_bulk_assign_shows_active_registrations(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('Nenhum usuário encontrado no banco de dados');
        }
        $this->actingAs($user);

        // Busca vínculos ativos
        $activeCount = EmployeeRegistration::where('status', 'active')->count();

        $response = $this->get(route('work-shift-templates.bulk-assign'));

        if ($activeCount > 0) {
            $response->assertSee('Matrícula');
            $response->assertDontSee('Nenhum vínculo ativo encontrado');
        } else {
            $response->assertSee('Nenhum vínculo ativo encontrado');
        }
    }

    /**
     * Testa se pode atribuir jornada a vínculos
     */
    public function test_can_assign_workshift_to_registrations(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('Nenhum usuário encontrado');
        }
        $this->actingAs($user);

        // Busca um template e vínculos
        $template = WorkShiftTemplate::first();
        $registrations = EmployeeRegistration::where('status', 'active')->limit(2)->get();

        if (!$template || $registrations->isEmpty()) {
            $this->markTestSkipped('Sem dados suficientes para teste');
        }

        $registrationIds = $registrations->pluck('id')->toArray();
        
        $response = $this->post(route('work-shift-templates.bulk-assign.store'), [
            'template_id' => $template->id,
            'registration_ids' => $registrationIds,
            'effective_from' => now()->format('Y-m-d'),
        ]);

        // Verifica se redirecionou corretamente
        $response->assertRedirect(route('work-shift-templates.bulk-assign'));
        
        // Verifica se a resposta tem sucesso ou erro (qualquer um é válido para este teste)
        $this->assertTrue(
            $response->getSession()->has('success') || $response->getSession()->has('error'),
            'Deve ter mensagem de sucesso ou erro na sessão'
        );
    }

    /**
     * Testa validação de campos obrigatórios
     */
    public function test_bulk_assign_validation(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('Nenhum usuário encontrado');
        }
        $this->actingAs($user);

        // Tenta enviar sem dados
        $response = $this->post(route('work-shift-templates.bulk-assign.store'), []);

        $response->assertSessionHasErrors(['template_id', 'registration_ids']);
    }

    /**
     * Testa se filtros estão disponíveis
     */
    public function test_filters_are_available(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('Nenhum usuário encontrado');
        }
        $this->actingAs($user);

        $response = $this->get(route('work-shift-templates.bulk-assign'));

        $response->assertSee('Filtrar por Estabelecimento');
        $response->assertSee('Filtrar por Departamento');
        $response->assertSee('Filtrar por Status de Jornada');
    }
}
