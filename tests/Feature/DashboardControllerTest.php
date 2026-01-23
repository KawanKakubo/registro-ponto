<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Person;
use App\Models\EmployeeRegistration;
use App\Models\Establishment;
use App\Models\Department;
use App\Models\WorkShiftTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_dashboard_loads_successfully(): void
    {
        $user = User::first();
        $this->actingAs($user);
        
        $response = $this->get(route('dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    public function test_dashboard_has_required_data(): void
    {
        $user = User::first();
        $this->actingAs($user);
        
        $response = $this->get(route('dashboard'));
        
        $response->assertViewHas('stats');
        $response->assertViewHas('alerts');
        $response->assertViewHas('charts');
        $response->assertViewHas('recentActivity');
    }

    public function test_dashboard_shows_correct_people_count(): void
    {
        $user = User::first();
        $this->actingAs($user);
        
        $peopleCount = Person::count();
        
        $response = $this->get(route('dashboard'));
        
        $response->assertSee($peopleCount);
    }

    public function test_dashboard_shows_active_registrations_count(): void
    {
        $user = User::first();
        $this->actingAs($user);
        
        $activeCount = EmployeeRegistration::where('status', 'active')->count();
        
        $response = $this->get(route('dashboard'));
        
        $response->assertSee($activeCount);
    }

    public function test_dashboard_shows_establishments_count(): void
    {
        $user = User::first();
        $this->actingAs($user);
        
        $establishmentsCount = Establishment::count();
        
        $response = $this->get(route('dashboard'));
        
        $response->assertSee($establishmentsCount);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('dashboard'));
        
        $response->assertRedirect(route('login'));
    }
}
