<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Person;
use App\Models\EmployeeRegistration;
use App\Models\Establishment;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TimesheetControllerTest extends TestCase
{
    /**
     * Test index page loads
     */
    public function test_index_page_loads(): void
    {
        // Create a test user and authenticate
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }
        
        $response = $this->actingAs($user)->get('/timesheets');
        
        $response->assertStatus(200);
        $response->assertViewIs('timesheets.index');
    }
    
    /**
     * Test search person by CPF
     */
    public function test_search_person_by_cpf(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }
        
        $person = Person::with('employeeRegistrations')->first();
        
        if (!$person) {
            $this->markTestSkipped('No person found in database');
        }
        
        $response = $this->actingAs($user)->post('/timesheets/search-person', [
            'search' => $person->cpf
        ]);
        
        // When only one person is found, it returns the select-registrations view
        $response->assertStatus(200);
        $response->assertViewIs('timesheets.select-registrations');
        $response->assertViewHas('person', $person);
    }
    
        /**
     * Test show person registrations page
     */
    public function test_show_person_registrations(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }
        
        $person = Person::with('activeRegistrations')->first();
        
        if (!$person) {
            $this->markTestSkipped('No person found in database');
        }
        
        if ($person->activeRegistrations->isEmpty()) {
            $this->markTestSkipped('Person has no active registrations');
        }
        
        $response = $this->actingAs($user)->get(route('timesheets.person-registrations', $person->id));
        
        $response->assertStatus(200);
        $response->assertViewIs('timesheets.select-registrations');
        $response->assertViewHas('person');
    }
    
    /**
     * Test show single registration
     */
    public function test_show_single_registration(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }
        
        $registration = EmployeeRegistration::with('person')->first();
        
        if (!$registration) {
            $this->markTestSkipped('No registration found in database');
        }
        
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        
        $response = $this->actingAs($user)->get(route('timesheets.show-registration', [
            'registration' => $registration->id,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]));
        
        $response->assertStatus(200);
        $response->assertViewIs('timesheets.show');
        $response->assertViewHas('registration', $registration);
        $response->assertViewHas('person', $registration->person);
    }
}
