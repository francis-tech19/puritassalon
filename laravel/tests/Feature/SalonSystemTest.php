<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SalonSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee("Purita's Beauty Lounge", false);
        $response->assertSee('Back to homepage');
        $response->assertSee('Book Your Visit');
        $response->assertSee('Enjoy Rewards');
    }

    public function test_registration_page_shows_booking_context(): void
    {
        $response = $this->get('/register?service=3&return=home%3Fsection%3Dservices');

        $response->assertStatus(200);
        $response->assertSee("You're booking a service.", false);
        $response->assertSee('name="service" value="3"', false);
        $response->assertSee('Back to services');
        $response->assertSee('Use at least 8 characters, including one uppercase letter and one number.');
        $response->assertSee('data-password-target="password"', false);
    }

    public function test_login_page_preserves_booking_context(): void
    {
        $response = $this->get('/login?service=3&return=home%3Fsection%3Dservices');

        $response->assertStatus(200);
        $response->assertSee('name="service" value="3"', false);
        $response->assertSee('name="return" value="home?section=services"', false);
        $response->assertSee('Back to services');
        $response->assertSee('/register?service=3&amp;return=home%3Fsection%3Dservices', false);
    }

    public function test_public_home_navigation_renders_only_the_selected_section(): void
    {
        $home = $this->get('/');
        $services = $this->get('/?section=services');
        $about = $this->get('/?section=about');
        $contact = $this->get('/?section=contact');

        $home->assertSee('Look good.', false)->assertDontSee('Featured Services');
        $services->assertSee('Featured Services')->assertDontSee('Look good.', false)->assertDontSee('Why choose');
        $about->assertSee('Why choose')->assertSee('How It Works')->assertDontSee('Poblacion Public Market');
        $contact->assertSee('Poblacion Public Market, San Juan, Batangas')
            ->assertSee('09611556557')
            ->assertSee('09192001649')
            ->assertSee('dcsisters@yahoo.com')
            ->assertSee('10:00 AM')
            ->assertSee('4:00 PM')
            ->assertSee('9:00 AM')
            ->assertSee('5:00 PM')
            ->assertDontSee('Featured Services')
            ->assertDontSee('Why choose');
    }

    public function test_registration_requires_a_strong_customer_password(): void
    {
        $response = $this->from('/register')->post('/register', [
            'full_name' => 'Test Customer',
            'email' => 'test-customer@example.com',
            'phone' => '09170000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
    }

    public function test_owner_authentication_and_dashboard_access(): void
    {
        $response = $this->post('/login', [
            'username' => 'owner',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Salon Dashboard');
        $dashboardResponse->assertSee('Purita Santos');
    }

    public function test_staff_authentication_and_dashboard_access(): void
    {
        $response = $this->post('/login', [
            'username' => 'staff1',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/employee-dashboard');
        $dashboardResponse = $this->get('/employee-dashboard');

        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('My Workday');
        $dashboardResponse->assertSee('Today’s schedule');
        $dashboardResponse->assertSee('My Dashboard');
    }

    public function test_owner_dashboard_shows_appointment_actions(): void
    {
        $owner = User::where('username', 'owner')->firstOrFail();
        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Confirm');
        $response->assertSee('Mark Arrived');
        $response->assertSee('Cancel');
    }

    public function test_seeded_customer_can_login_to_customer_dashboard(): void
    {
        $response = $this->post('/login', [
            'username' => 'customer',
            'password' => 'customer123',
        ]);

        $response->assertRedirect('/customer-dashboard');
        $this->assertAuthenticatedAs(User::where('username', 'customer')->first());
    }

    public function test_customer_dashboard_does_not_show_staff_navigation(): void
    {
        $customer = Customer::firstOrFail();
        $customerUser = $this->createCustomerUser($customer, 'navigation');

        $response = $this->actingAs($customerUser)->get('/customer-dashboard');

        $response->assertStatus(200);
        $response->assertSee('My Dashboard');
        $response->assertSee('Book Appointment');
        $response->assertSee('My Appointments');
        $response->assertSee('customer-dashboard?view=booking', false);
        $response->assertSee('customer-dashboard?view=profile', false);
        $response->assertSee('mobileNav = false; sidebarOpen = false', false);
        $response->assertDontSee('Point of Sale');
        $response->assertDontSee('Front Desk &amp; Services', false);
    }

    public function test_customer_dashboard_sidebar_views_show_only_selected_area(): void
    {
        $customerUser = $this->createCustomerUser(Customer::firstOrFail(), 'views');

        $bookingResponse = $this->actingAs($customerUser)->get('/customer-dashboard?view=booking');
        $bookingResponse->assertSee('Available services');
        $bookingResponse->assertSee("customerView === 'booking'", false);

        $appointmentsResponse = $this->actingAs($customerUser)->get('/customer-dashboard?view=appointments');
        $appointmentsResponse->assertSee('Upcoming appointments');
        $appointmentsResponse->assertSee("customerView === 'appointments'", false);

        $profileResponse = $this->actingAs($customerUser)->get('/customer-dashboard?view=profile');
        $profileResponse->assertSee('Profile');
        $profileResponse->assertSee("customerView === 'profile'", false);
    }

    public function test_customer_notifications_only_show_appointment_messages(): void
    {
        $customer = Customer::firstOrFail();
        $customerUser = $this->createCustomerUser($customer, 'notifications');
        Notification::create([
            'user_id' => null,
            'title' => 'Low Stock Warning',
            'message' => 'Internal inventory message',
            'type' => 'INVENTORY',
        ]);
        Notification::create([
            'user_id' => $customerUser->id,
            'title' => 'Appointment confirmed',
            'message' => 'Your appointment is confirmed.',
            'type' => 'APPOINTMENT',
        ]);

        $response = $this->actingAs($customerUser)->get('/notifications');

        $response->assertStatus(200);
        $response->assertSee('Your appointment is confirmed.');
        $response->assertDontSee('Internal inventory message');
    }

    public function test_admin_can_login_with_admin_password(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin-dashboard');
        $this->assertAuthenticatedAs(User::where('username', 'admin')->first());
    }

    public function test_appointments_page_renders(): void
    {
        $user = User::where('username', 'owner')->first();
        $response = $this->actingAs($user)->get('/appointments');

        $response->assertStatus(200);
        $response->assertSee('Appointments Booking');
    }

    public function test_customer_dashboard_shows_appointment_proof_details(): void
    {
        $customer = Customer::firstOrFail();
        $customer->update(['address' => '42 Sample Street, Quezon City']);
        $customer->appointments()->firstOrFail()->update(['status' => 'CONFIRMED', 'appointment_date' => now()->toDateString()]);
        $customerUser = $this->createCustomerUser($customer, 'proof');

        $response = $this->actingAs($customerUser)->get('/customer-dashboard');

        $response->assertStatus(200);
        $response->assertSee('Appointment proof');
        $response->assertSee('42 Sample Street, Quezon City');
        $response->assertSee('Download proof image');
        $response->assertSee('data-proof-phone=', false);
    }

    public function test_customer_can_update_address_for_appointment_proof(): void
    {
        $customer = Customer::firstOrFail();
        $customerUser = $this->createCustomerUser($customer, 'address');

        $response = $this->actingAs($customerUser)->put(route('customer.profile.update'), [
            'full_name' => $customer->full_name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => '88 New Address Road, Manila',
        ]);

        $response->assertSessionHas('success');
        $this->assertSame('88 New Address Road, Manila', $customer->fresh()->address);
    }

    public function test_point_of_sale_page_renders(): void
    {
        $user = User::where('username', 'owner')->first();
        $response = $this->actingAs($user)->get('/sales');

        $response->assertStatus(200);
        $response->assertSee('Point of Sale (POS)');
        $response->assertSee('Current Bill');
    }

    public function test_customers_page_renders(): void
    {
        $user = User::where('username', 'owner')->first();
        $response = $this->actingAs($user)->get('/customers');

        $response->assertStatus(200);
        $response->assertSee('Client Directory');
        $response->assertSee('Ana Maria Gonzales');
    }

    public function test_inventory_page_renders(): void
    {
        $user = User::where('username', 'owner')->first();
        $response = $this->actingAs($user)->get('/inventory');

        $response->assertStatus(200);
        $response->assertSee('Salon Inventory');
    }

    public function test_inventory_logs_by_product_renders(): void
    {
        $user = User::where('username', 'owner')->first();
        $inventory = Inventory::firstOrFail();

        // 1. All logs view
        $response = $this->actingAs($user)->get('/inventory/logs');
        $response->assertStatus(200);
        $response->assertSee('Product Logs');

        // 2. Perform adjustment to generate a specific log
        $this->actingAs($user)->post(route('inventory.adjust', $inventory), [
            'transaction_type' => 'RESTOCK',
            'quantity_change' => 15,
            'notes' => 'Bulk restock shipment #994',
        ]);

        // 3. Logs filtered by specific product
        $productLogsResponse = $this->actingAs($user)->get(route('inventory.logs', ['product_id' => $inventory->id]));
        $productLogsResponse->assertStatus(200);
        $productLogsResponse->assertSee($inventory->item_name);
        $productLogsResponse->assertSee('Selected Product Focus');
        $productLogsResponse->assertSee('Bulk restock shipment #994');

        // 4. Logs filtered by movement action and search
        $filteredResponse = $this->actingAs($user)->get(route('inventory.logs', [
            'product_id' => $inventory->id,
            'action' => 'RESTOCK',
            'search' => '994',
        ]));
        $filteredResponse->assertStatus(200);
        $filteredResponse->assertSee('Bulk restock shipment #994');
    }

    public function test_admin_console_access_for_admin(): void
    {
        $admin = User::where('username', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin-dashboard');

        $response->assertStatus(200);
        $response->assertSee('Administrator Console');
    }

    public function test_customer_cannot_access_operational_routes(): void
    {
        $customer = Customer::first();
        $customerUser = User::create([
            'username' => 'customer-test',
            'name' => $customer->full_name,
            'email' => 'customer-test@example.com',
            'password' => 'Password123',
            'role_id' => Role::firstOrCreate(['role_name' => 'CUSTOMER'])->id,
            'customer_id' => $customer->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($customerUser)->get('/appointments');

        $response->assertRedirect('/customer-dashboard');
    }

    public function test_customer_cannot_update_staff_appointment(): void
    {
        $customer = Customer::first();
        $customerUser = User::create([
            'username' => 'customer-mutation-test',
            'name' => $customer->full_name,
            'email' => 'customer-mutation@example.com',
            'password' => 'Password123',
            'role_id' => Role::firstOrCreate(['role_name' => 'CUSTOMER'])->id,
            'customer_id' => $customer->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $appointment = $customer->appointments()->first();

        $response = $this->actingAs($customerUser)->patch(route('appointments.status', $appointment), [
            'status' => 'CANCELLED',
        ]);

        $response->assertRedirect('/customer-dashboard');
        $this->assertNotSame('CANCELLED', $appointment->fresh()->status);
    }

    public function test_customer_cannot_access_operational_api(): void
    {
        $customer = Customer::first();
        $customerUser = User::create([
            'username' => 'customer-api-test',
            'name' => $customer->full_name,
            'email' => 'customer-api@example.com',
            'password' => 'Password123',
            'role_id' => Role::firstOrCreate(['role_name' => 'CUSTOMER'])->id,
            'customer_id' => $customer->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($customerUser)->getJson('/api/appointments');

        $response->assertForbidden();
        $response->assertJson(['success' => false]);
    }

    public function test_customer_can_reschedule_their_pending_appointment(): void
    {
        $customer = Customer::first();
        $customerUser = $this->createCustomerUser($customer, 'reschedule');
        $appointment = $customer->appointments()->firstOrFail();
        $appointment->update(['status' => 'PENDING']);

        $response = $this->actingAs($customerUser)->patch(route('customer.appointments.reschedule', $appointment), [
            'appointment_date' => now()->addDays(2)->toDateString(),
            'start_time' => '14:00',
        ]);

        $response->assertSessionHas('success');
        $this->assertSame(now()->addDays(2)->toDateString(), $appointment->fresh()->appointment_date);
        $this->assertSame('14:00:00', $appointment->fresh()->start_time);
    }

    public function test_customer_cannot_reschedule_completed_appointment(): void
    {
        $customer = Customer::first();
        $customerUser = $this->createCustomerUser($customer, 'completed');
        $appointment = $customer->appointments()->firstOrFail();
        $appointment->update(['status' => 'COMPLETED']);

        $response = $this->actingAs($customerUser)->patch(route('customer.appointments.reschedule', $appointment), [
            'appointment_date' => now()->addDays(2)->toDateString(),
            'start_time' => '14:00',
        ]);

        $response->assertSessionHasErrors('appointment');
    }

    public function test_customer_booking_rejects_time_outside_salon_hours(): void
    {
        $customer = Customer::first();
        $customerUser = $this->createCustomerUser($customer, 'availability');
        $service = Service::where('status', 'ACTIVE')->first();
        $employee = $service ? Employee::where('status', 'ACTIVE')->first() : null;

        $response = $this->actingAs($customerUser)->from('/customer-dashboard')->post(route('customer.appointments.book'), [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:00',
        ]);

        $response->assertRedirect('/customer-dashboard');
        $response->assertSessionHasErrors('start_time');
    }

    public function test_customer_booking_is_confirmed_and_reserves_the_service_duration(): void
    {
        $customer = Customer::firstOrFail();
        $customerUser = $this->createCustomerUser($customer, 'duration');
        $service = Service::where('status', 'ACTIVE')->firstOrFail();
        $service->update(['duration_minutes' => 30]);
        $employee = Employee::where('status', 'ACTIVE')->firstOrFail();
        $appointmentDate = now()->addDays(30)->toDateString();

        $response = $this->actingAs($customerUser)->from('/customer-dashboard')->post(route('customer.appointments.book'), [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_date' => $appointmentDate,
            'start_time' => '14:00',
        ]);

        $response->assertRedirect('/customer-dashboard');
        $this->assertDatabaseHas('appointments', [
            'customer_id' => $customer->id,
            'employee_id' => $employee->id,
            'appointment_date' => $appointmentDate,
            'start_time' => '14:00:00',
            'end_time' => '14:30:00',
            'status' => 'CONFIRMED',
        ]);
        $this->assertDatabaseHas('notifications', [
            'appointment_id' => Appointment::where('customer_id', $customer->id)
                ->where('appointment_date', $appointmentDate)
                ->where('start_time', '14:00:00')
                ->value('id'),
            'title' => 'Appointment confirmed',
            'type' => 'APPOINTMENT',
        ]);
    }

    public function test_customer_booking_rejects_overlap_using_each_services_duration(): void
    {
        $firstCustomer = Customer::firstOrFail();
        $firstUser = $this->createCustomerUser($firstCustomer, 'short-service');
        $secondCustomer = Customer::create([
            'customer_code' => 'CUST-OVERLAP',
            'full_name' => 'Second Test Customer',
            'phone' => '09170000001',
            'email' => 'second-overlap@example.com',
        ]);
        $secondUser = $this->createCustomerUser($secondCustomer, 'long-service');
        $services = Service::where('status', 'ACTIVE')->take(2)->get();
        $this->assertCount(2, $services);
        $shortService = $services[0];
        $longService = $services[1];
        $shortService->update(['duration_minutes' => 30]);
        $longService->update(['duration_minutes' => 120]);
        $employee = Employee::where('status', 'ACTIVE')->firstOrFail();
        $appointmentDate = now()->addDays(30)->toDateString();

        $this->actingAs($firstUser)->post(route('customer.appointments.book'), [
            'service_id' => $shortService->id,
            'employee_id' => $employee->id,
            'appointment_date' => $appointmentDate,
            'start_time' => '12:00',
        ])->assertRedirect();

        $this->actingAs($secondUser)->postJson(route('customer.appointments.book'), [
            'service_id' => $longService->id,
            'employee_id' => $employee->id,
            'appointment_date' => $appointmentDate,
            'start_time' => '12:15',
        ])->assertStatus(409)->assertJsonPath('message', 'This appointment time is no longer available. Please select another available time.');

        $this->assertDatabaseHas('appointments', [
            'customer_id' => $firstCustomer->id,
            'start_time' => '12:00:00',
            'end_time' => '12:30:00',
            'status' => 'CONFIRMED',
        ]);
        $this->assertDatabaseMissing('appointments', [
            'customer_id' => $secondCustomer->id,
            'appointment_date' => $appointmentDate,
            'start_time' => '12:15:00',
        ]);

        $this->actingAs($secondUser)->post(route('customer.appointments.book'), [
            'service_id' => $longService->id,
            'employee_id' => $employee->id,
            'appointment_date' => $appointmentDate,
            'start_time' => '12:30',
        ])->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'customer_id' => $secondCustomer->id,
            'appointment_date' => $appointmentDate,
            'start_time' => '12:30:00',
            'end_time' => '14:30:00',
            'status' => 'CONFIRMED',
        ]);
    }

    public function test_availability_slots_are_employee_specific_and_use_service_duration(): void
    {
        $service = Service::where('status', 'ACTIVE')->firstOrFail();
        $service->update(['duration_minutes' => 120]);
        $employee = Employee::where('status', 'ACTIVE')->firstOrFail();
        $otherEmployee = Employee::create([
            'employee_code' => 'EMP-AVAIL',
            'full_name' => 'Other Available Stylist',
            'position' => 'Stylist',
            'status' => 'ACTIVE',
        ]);
        $appointmentDate = now()->addDays(30)->toDateString();
        Appointment::create([
            'appointment_code' => 'APT-SLOT-TEST',
            'customer_id' => Customer::firstOrFail()->id,
            'employee_id' => $employee->id,
            'appointment_date' => $appointmentDate,
            'start_time' => '12:00:00',
            'end_time' => '14:00:00',
            'status' => 'CONFIRMED',
            'total_amount' => $service->price,
        ]);
        $customerUser = $this->createCustomerUser(Customer::firstOrFail(), 'slots');

        $employeeSlots = $this->actingAs($customerUser)->getJson(route('customer.appointments.availability', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_date' => $appointmentDate,
        ]));
        $otherEmployeeSlots = $this->getJson(route('customer.appointments.availability', [
            'service_id' => $service->id,
            'employee_id' => $otherEmployee->id,
            'appointment_date' => $appointmentDate,
        ]));

        $employeeSlots->assertOk()->assertJsonPath('duration_minutes', 120);
        $employeeSlots->assertJsonPath('slots.8.available', false);
        $employeeSlots->assertJsonPath('slots.16.available', true);
        $otherEmployeeSlots->assertJsonPath('slots.8.available', true);
    }

    public function test_available_slots_follow_weekday_and_weekend_business_hours(): void
    {
        $service = Service::where('status', 'ACTIVE')->firstOrFail();
        $service->update(['duration_minutes' => 30]);
        $employee = Employee::create([
            'employee_code' => 'EMP-HOURS-TEST',
            'full_name' => 'Hours Test Stylist',
            'position' => 'Stylist',
            'status' => 'ACTIVE',
        ]);
        $customerUser = $this->createCustomerUser(Customer::firstOrFail(), 'hours');
        $weekdayDate = now()->next('Monday')->toDateString();
        $weekendDate = now()->next('Saturday')->toDateString();

        $weekdaySlots = $this->actingAs($customerUser)->getJson(route('customer.appointments.availability', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_date' => $weekdayDate,
        ]));
        $weekendSlots = $this->getJson(route('customer.appointments.availability', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_date' => $weekendDate,
        ]));

        $weekdaySlots->assertOk()
            ->assertJsonPath('slots.0.start_time', '10:00')
            ->assertJsonPath('slots.22.end_time', '16:00');
        $weekendSlots->assertOk()
            ->assertJsonPath('slots.0.start_time', '09:00')
            ->assertJsonPath('slots.30.end_time', '17:00');
    }

    public function test_assigned_employee_can_mark_customer_arrived(): void
    {
        $employee = Employee::where('status', 'ACTIVE')->firstOrFail();
        $staff = User::whereHas('role', fn ($query) => $query->where('role_name', 'STAFF'))->firstOrFail();
        $staff->update(['employee_id' => $employee->id]);
        $appointment = Appointment::firstOrFail();
        $appointment->update([
            'employee_id' => $employee->id,
            'status' => 'CONFIRMED',
            'arrival_status' => 'NOT_ARRIVED',
            'arrival_time' => null,
            'arrived_marked_by' => null,
        ]);
        $this->freezeTime();

        $response = $this->actingAs($staff)->patch(route('appointments.arrival', $appointment), [
            'arrival_status' => 'ARRIVED',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'arrival_status' => 'ARRIVED',
            'arrived_marked_by' => $staff->id,
        ]);
        $this->assertSame(now()->toDateTimeString(), $appointment->fresh()->arrival_time->toDateTimeString());
    }

    public function test_employee_cannot_mark_another_employees_customer_arrived(): void
    {
        $employee = Employee::where('status', 'ACTIVE')->firstOrFail();
        $otherEmployee = Employee::create([
            'employee_code' => 'EMP-OTHER',
            'full_name' => 'Other Assigned Stylist',
            'position' => 'Stylist',
            'status' => 'ACTIVE',
        ]);
        $staff = User::whereHas('role', fn ($query) => $query->where('role_name', 'STAFF'))->firstOrFail();
        $staff->update(['employee_id' => $otherEmployee->id]);
        $appointment = Appointment::firstOrFail();
        $appointment->update(['employee_id' => $employee->id, 'arrival_status' => 'NOT_ARRIVED']);

        $this->actingAs($staff)->patch(route('appointments.arrival', $appointment), [
            'arrival_status' => 'ARRIVED',
        ])->assertStatus(403);

        $this->assertSame('NOT_ARRIVED', $appointment->fresh()->arrival_status);
    }

    public function test_inventory_adjustment_records_stock_snapshots_and_rejects_negative_stock(): void
    {
        $owner = User::where('username', 'owner')->firstOrFail();
        $inventory = Inventory::firstOrFail();
        $inventory->update(['quantity' => 20, 'status' => 'IN_STOCK']);

        $this->actingAs($owner)->post(route('inventory.adjust', $inventory), [
            'transaction_type' => 'ADJUSTMENT',
            'quantity_change' => -2,
            'notes' => 'Cycle count correction',
        ])->assertSessionHas('success');

        $this->assertSame(18, $inventory->fresh()->quantity);
        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_id' => $inventory->id,
            'action' => 'ADJUSTMENT',
            'quantity_change' => -2,
            'previous_stock' => 20,
            'new_stock' => 18,
            'recorded_by' => $owner->id,
            'notes' => 'Cycle count correction',
        ]);
        $transactionsBeforeRejectedMovement = InventoryTransaction::where('inventory_id', $inventory->id)->count();

        $this->from(route('inventory.index'))->post(route('inventory.adjust', $inventory), [
            'transaction_type' => 'STOCK_OUT',
            'quantity_change' => 19,
            'notes' => 'Attempted overdraw',
        ])->assertSessionHasErrors('quantity_change');

        $this->assertSame(18, $inventory->fresh()->quantity);
        $this->assertSame($transactionsBeforeRejectedMovement, InventoryTransaction::where('inventory_id', $inventory->id)->count());
    }

    public function test_point_of_sale_records_inventory_sale_log(): void
    {
        $owner = User::where('username', 'owner')->firstOrFail();
        $inventory = Inventory::firstOrFail();
        $inventory->update(['quantity' => 20, 'status' => 'IN_STOCK']);

        $this->actingAs($owner)->post(route('sales.store'), [
            'payment_method' => 'CASH',
            'items' => [[
                'item_type' => 'PRODUCT',
                'item_id' => $inventory->id,
                'item_name' => $inventory->item_name,
                'quantity' => 2,
                'unit_price' => 100,
            ]],
        ])->assertRedirect();

        $this->assertSame(18, $inventory->fresh()->quantity);
        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_id' => $inventory->id,
            'action' => 'SALE',
            'quantity_change' => -2,
            'previous_stock' => 20,
            'new_stock' => 18,
            'recorded_by' => $owner->id,
        ]);
    }

    public function test_appointment_reminder_command_creates_each_reminder_once(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        Appointment::query()->update(['appointment_date' => today()->addDays(60)->toDateString()]);
        $customer = Customer::firstOrFail();
        $service = Service::where('status', 'ACTIVE')->firstOrFail();
        $employee = Employee::where('status', 'ACTIVE')->firstOrFail();
        $appointment = Appointment::create([
            'appointment_code' => 'APT-REMINDER',
            'customer_id' => $customer->id,
            'employee_id' => $employee->id,
            'appointment_date' => today()->toDateString(),
            'start_time' => '10:30:00',
            'end_time' => '11:30:00',
            'status' => 'CONFIRMED',
            'arrival_status' => 'NOT_ARRIVED',
            'total_amount' => $service->price,
        ]);
        AppointmentService::create([
            'appointment_id' => $appointment->id,
            'service_id' => $service->id,
            'price_at_booking' => $service->price,
        ]);

        Artisan::call('appointments:send-reminders');
        Artisan::call('appointments:send-reminders');

        $this->assertDatabaseHas('appointment_notification_events', [
            'appointment_id' => $appointment->id,
            'event_key' => 'reminder_30_'.today()->format('Ymd').'1030',
        ]);
        $this->assertSame(1, \App\Models\Notification::where('appointment_id', $appointment->id)->count());
    }

    public function test_health_api_endpoint(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'HEALTHY',
        ]);
    }

    private function createCustomerUser(Customer $customer, string $suffix): User
    {
        return User::create([
            'username' => 'customer-'.$suffix,
            'name' => $customer->full_name,
            'email' => 'customer-'.$suffix.'@example.com',
            'password' => 'Password123',
            'role_id' => Role::firstOrCreate(['role_name' => 'CUSTOMER'])->id,
            'customer_id' => $customer->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
