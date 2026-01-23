<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Person;
use App\Models\EmployeeRegistration;
use App\Models\User;
use App\Models\Establishment;
use App\Models\Department;

class EmployeeControllerTest extends TestCase
{
    /**
     * Test index page loads with people
     */
    public function test_index_page_loads(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }
        
        $response = $this->actingAs($user)->get('/employees');
        
        $response->assertStatus(200);
        $response->assertViewIs('employees.index');
        $response->assertViewHas('people');
    }
    
    /**
     * Test show person page
     */
    public function test_show_person_page(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }
        
        $person = Person::with('employeeRegistrations')->first();
        if (!$person) {
            $this->markTestSkipped('No person found in database');
        }
        
        $response = $this->actingAs($user)->get('/employees/' . $person->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('employees.show');
        $response->assertViewHas('person', $person);
    }
    
    /**
     * Test create person form loads
     */
    public function test_create_person_form_loads(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }
        
        $response = $this->actingAs($user)->get('/employees/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('employees.create');
        $response->assertViewHas(['establishments', 'departments']);
    }
    
    /**
     * Test edit person form loads
     */
    public function test_edit_person_form_loads(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }
        
        $person = Person::first();
        if (!$person) {
            $this->markTestSkipped('No person found in database');
        }
        
        $response = $this->actingAs($user)->get('/employees/' . $person->id . '/edit');
        
        $response->assertStatus(200);
        $response->assertViewIs('employees.edit');
        $response->assertViewHas('person', $person);
    }
    
    /**
     * Test create registration form loads
     */
    public function test_create_registration_form_loads(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }
        
        $person = Person::first();
        if (!$person) {
            $this->markTestSkipped('No person found in database');
        }
        
        $response = $this->actingAs($user)->get('/people/' . $person->id . '/registrations/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('employee_registrations.create');
        $response->assertViewHas(['person', 'establishments', 'departments']);
    }
    
    /**
     * Test edit registration form loads
     */
    public function test_edit_registration_form_loads(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }
        
        $registration = EmployeeRegistration::with('person')->first();
        if (!$registration) {
            $this->markTestSkipped('No registration found in database');
        }
        
        $response = $this->actingAs($user)->get('/registrations/' . $registration->id . '/edit');
        
        $response->assertStatus(200);
        $response->assertViewIs('employee_registrations.edit');
        $response->assertViewHas(['registration', 'establishments', 'departments']);
    }
}
