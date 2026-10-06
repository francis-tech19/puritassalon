<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\AuditLog;
use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\LoyaltyReward;
use App\Models\LoyaltySetting;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $ownerRole = Role::firstOrCreate(['role_name' => 'OWNER']);
        $staffRole = Role::firstOrCreate(['role_name' => 'STAFF']);
        $adminRole = Role::firstOrCreate(['role_name' => 'ADMIN']);
        $customerRole = Role::firstOrCreate(['role_name' => 'CUSTOMER']);

        // 2. Employees
        $emp1 = Employee::create([
            'employee_code' => 'EMP-001',
            'full_name' => 'Purita Santos',
            'phone' => '0917-111-2233',
            'email' => 'purita@beauty.com',
            'position' => 'Salon Owner & Master Stylist',
            'status' => 'ACTIVE',
            'schedule_notes' => 'Mon - Sat (9:00 AM - 6:00 PM)',
        ]);

        $emp2 = Employee::create([
            'employee_code' => 'EMP-002',
            'full_name' => 'Maria Clara Dela Cruz',
            'phone' => '0918-222-3344',
            'email' => 'maria@beauty.com',
            'position' => 'Senior Hair Stylist',
            'status' => 'ACTIVE',
            'schedule_notes' => 'Tue - Sun (9:00 AM - 7:00 PM)',
        ]);

        $emp3 = Employee::create([
            'employee_code' => 'EMP-003',
            'full_name' => 'Angela Reyes',
            'phone' => '0919-333-4455',
            'email' => 'angela@beauty.com',
            'position' => 'Nail & Facial Technician',
            'status' => 'ACTIVE',
            'schedule_notes' => 'Mon - Fri (10:00 AM - 6:00 PM)',
        ]);

        $emp4 = Employee::create([
            'employee_code' => 'EMP-004',
            'full_name' => 'Jenny Mercado',
            'phone' => '0920-444-5566',
            'email' => 'jenny@beauty.com',
            'position' => 'Junior Stylist',
            'status' => 'ACTIVE',
            'schedule_notes' => 'Wed - Mon (9:00 AM - 5:00 PM)',
        ]);

        // 3. Users
        $hashedPassword = Hash::make('password123');
        $adminPassword = Hash::make('admin123');

        $userOwner = User::create([
            'username' => 'owner',
            'name' => 'Purita Santos',
            'email' => 'owner@purita.com',
            'password' => $hashedPassword,
            'role_id' => $ownerRole->id,
            'employee_id' => $emp1->id,
            'is_active' => true,
        ]);

        $userStaff = User::create([
            'username' => 'staff1',
            'name' => 'Maria Clara Dela Cruz',
            'email' => 'staff@purita.com',
            'password' => $hashedPassword,
            'role_id' => $staffRole->id,
            'employee_id' => $emp2->id,
            'is_active' => true,
        ]);

        $userAdmin = User::create([
            'username' => 'admin',
            'name' => 'System Administrator',
            'email' => 'admin@purita.com',
            'password' => $adminPassword,
            'role_id' => $adminRole->id,
            'employee_id' => $emp1->id,
            'is_active' => true,
        ]);

        // 4. Customers
        $cust1 = Customer::create([
            'customer_code' => 'CUST-001',
            'full_name' => 'Ana Maria Gonzales',
            'phone' => '0917-555-0101',
            'email' => 'ana.gonzales@gmail.com',
            'address' => 'Quezon City, Metro Manila',
            'notes' => 'Prefers organic hair color treatments',
            'status' => 'ACTIVE',
            'visit_count' => 6,
            'total_spent' => 4500.00,
        ]);

        $cust2 = Customer::create([
            'customer_code' => 'CUST-002',
            'full_name' => 'Bea Alonzo',
            'phone' => '0918-555-0202',
            'email' => 'bea.a@yahoo.com',
            'address' => 'Makati City',
            'notes' => 'Regular customer for re-bonding',
            'status' => 'ACTIVE',
            'visit_count' => 4,
            'total_spent' => 3800.00,
        ]);

        $cust3 = Customer::create([
            'customer_code' => 'CUST-003',
            'full_name' => 'Carla Abellana',
            'phone' => '0919-555-0303',
            'email' => 'carla.a@gmail.com',
            'address' => 'Pasig City',
            'notes' => 'Sensitive skin, test polish first',
            'status' => 'ACTIVE',
            'visit_count' => 2,
            'total_spent' => 1200.00,
        ]);

        $cust4 = Customer::create([
            'customer_code' => 'CUST-004',
            'full_name' => 'Donna Cruz',
            'phone' => '0920-555-0404',
            'email' => 'donna.cruz@outlook.com',
            'address' => 'Mandaluyong City',
            'notes' => 'Loves gel manicures',
            'status' => 'ACTIVE',
            'visit_count' => 5,
            'total_spent' => 2900.00,
        ]);

        $cust5 = Customer::create([
            'customer_code' => 'CUST-005',
            'full_name' => 'Elena Adarna',
            'phone' => '0921-555-0505',
            'email' => 'elena.a@gmail.com',
            'address' => 'San Juan City',
            'notes' => 'Walk-in customer',
            'status' => 'ACTIVE',
            'visit_count' => 1,
            'total_spent' => 450.00,
        ]);

        $demoCustomer = User::updateOrCreate(
            ['username' => 'customer'],
            [
                'name' => $cust1->full_name,
                'email' => 'customer@purita.com',
                'password' => Hash::make('customer123'),
                'role_id' => $customerRole->id,
                'customer_id' => $cust1->id,
                'is_active' => true,
            ]
        );
        $demoCustomer->forceFill(['email_verified_at' => now()])->save();

        // 5. Services
        $s1 = Service::create([
            'service_name' => 'Haircut & Blowdry (Women)',
            'category' => 'Hair Care',
            'description' => 'Precision haircut with washing and blowdry styling',
            'price' => 450.00,
            'duration_minutes' => 45,
            'status' => 'ACTIVE',
        ]);

        $s2 = Service::create([
            'service_name' => 'Haircut (Men)',
            'category' => 'Hair Care',
            'description' => 'Classic and modern men haircuts with hot towel finish',
            'price' => 300.00,
            'duration_minutes' => 30,
            'status' => 'ACTIVE',
        ]);

        $s3 = Service::create([
            'service_name' => 'Full Hair Coloring',
            'category' => 'Hair Care',
            'description' => 'Premium hair color with scalp protection and wash',
            'price' => 1500.00,
            'duration_minutes' => 90,
            'status' => 'ACTIVE',
        ]);

        $s4 = Service::create([
            'service_name' => 'Keratin Hair Rebonding',
            'category' => 'Hair Care',
            'description' => 'Intense hair smoothing and straightening treatment',
            'price' => 2500.00,
            'duration_minutes' => 150,
            'status' => 'ACTIVE',
        ]);

        $s5 = Service::create([
            'service_name' => 'Classic Manicure',
            'category' => 'Nail Care',
            'description' => 'Nail shaping, cuticle care, and polish application',
            'price' => 250.00,
            'duration_minutes' => 30,
            'status' => 'ACTIVE',
        ]);

        $s6 = Service::create([
            'service_name' => 'Classic Pedicure',
            'category' => 'Nail Care',
            'description' => 'Foot soak, scrub, nail shaping, and polish',
            'price' => 350.00,
            'duration_minutes' => 45,
            'status' => 'ACTIVE',
        ]);

        $s7 = Service::create([
            'service_name' => 'Gel Manicure & Pedicure Combo',
            'category' => 'Nail Care',
            'description' => 'Long-lasting gel polish manicure and pedicure package',
            'price' => 900.00,
            'duration_minutes' => 75,
            'status' => 'ACTIVE',
        ]);

        $s8 = Service::create([
            'service_name' => 'Facial Deep Cleansing',
            'category' => 'Facial & Skin',
            'description' => 'Deep pore cleansing, exfoliation, and soothing facial mask',
            'price' => 800.00,
            'duration_minutes' => 60,
            'status' => 'ACTIVE',
        ]);

        // 6. Inventory
        $inv1 = Inventory::create([
            'item_code' => 'INV-001',
            'item_name' => 'Organic Hair Color Cream (Dark Brown)',
            'category' => 'Hair Supplies',
            'quantity' => 12,
            'unit' => 'tubes',
            'min_stock_level' => 5,
            'supplier' => 'L’Oreal Philippines',
            'status' => 'IN_STOCK',
        ]);

        $inv2 = Inventory::create([
            'item_code' => 'INV-002',
            'item_name' => 'Keratin Straightening Solution 1000ml',
            'category' => 'Hair Supplies',
            'quantity' => 3,
            'unit' => 'bottles',
            'min_stock_level' => 4,
            'supplier' => 'Schwarzkopf Distributor',
            'status' => 'LOW_STOCK',
        ]);

        $inv3 = Inventory::create([
            'item_code' => 'INV-003',
            'item_name' => 'Clarifying Shampoo 500ml',
            'category' => 'Hair Supplies',
            'quantity' => 8,
            'unit' => 'bottles',
            'min_stock_level' => 3,
            'supplier' => 'Wella Professional',
            'status' => 'IN_STOCK',
        ]);

        $inv4 = Inventory::create([
            'item_code' => 'INV-004',
            'item_name' => 'Nail Polish Remover 500ml',
            'category' => 'Nail Supplies',
            'quantity' => 2,
            'unit' => 'bottles',
            'min_stock_level' => 3,
            'supplier' => 'Beauty Supplies Inc',
            'status' => 'LOW_STOCK',
        ]);

        $inv5 = Inventory::create([
            'item_code' => 'INV-005',
            'item_name' => 'Premium Red Gel Nail Polish',
            'category' => 'Nail Supplies',
            'quantity' => 15,
            'unit' => 'bottles',
            'min_stock_level' => 5,
            'supplier' => 'OPI Philippines',
            'status' => 'IN_STOCK',
        ]);

        $inv6 = Inventory::create([
            'item_code' => 'INV-006',
            'item_name' => 'Disposable Towels (Pack of 100)',
            'category' => 'General Salon',
            'quantity' => 1,
            'unit' => 'packs',
            'min_stock_level' => 2,
            'supplier' => 'Manila Hygiene Co',
            'status' => 'LOW_STOCK',
        ]);

        // Transactions
        InventoryTransaction::create([
            'inventory_id' => $inv1->id,
            'transaction_type' => 'STOCK_IN',
            'quantity_change' => 20,
            'notes' => 'Initial monthly stock delivery',
            'recorded_by' => $userOwner->id,
        ]);

        // 7. Business Settings
        BusinessSetting::create([
            'salon_name' => "Purita's Beauty Lounge",
            'opening_time' => '10:00:00',
            'closing_time' => '16:00:00',
            'weekend_opening_time' => '09:00:00',
            'weekend_closing_time' => '17:00:00',
            'contact_phone' => '09611556557',
            'contact_phone_secondary' => '09192001649',
            'contact_email' => 'dcsisters@yahoo.com',
            'address' => 'Poblacion Public Market, San Juan, Batangas',
        ]);

        // 8. Loyalty Settings
        LoyaltySetting::create([
            'visits_required_for_reward' => 5,
            'reward_description' => '10% Discount on Next Service for 5 Completed Visits',
            'discount_percentage' => 10.00,
            'is_active' => true,
        ]);

        // 9. Loyalty Rewards
        LoyaltyReward::create([
            'customer_id' => $cust1->id,
            'reward_title' => '10% Loyalty Reward (5 Visits Reached)',
            'discount_percentage' => 10.00,
            'status' => 'AVAILABLE',
            'issued_date' => now(),
        ]);

        // 10. Appointments
        $apt1 = Appointment::create([
            'appointment_code' => 'APT-'.date('Ymd').'-01',
            'customer_id' => $cust1->id,
            'employee_id' => $emp2->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '09:30:00',
            'end_time' => '11:00:00',
            'status' => 'COMPLETED',
            'total_amount' => 1500.00,
            'notes' => 'Hair color retouch',
            'created_by' => $userOwner->id,
        ]);
        AppointmentService::create([
            'appointment_id' => $apt1->id,
            'service_id' => $s3->id,
            'price_at_booking' => 1500.00,
        ]);

        $apt2 = Appointment::create([
            'appointment_code' => 'APT-'.date('Ymd').'-02',
            'customer_id' => $cust2->id,
            'employee_id' => $emp2->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '11:30:00',
            'end_time' => '14:00:00',
            'status' => 'CONFIRMED',
            'total_amount' => 2500.00,
            'notes' => 'Keratin Rebonding appointment',
            'created_by' => $userOwner->id,
        ]);
        AppointmentService::create([
            'appointment_id' => $apt2->id,
            'service_id' => $s4->id,
            'price_at_booking' => 2500.00,
        ]);

        $apt3 = Appointment::create([
            'appointment_code' => 'APT-'.date('Ymd').'-03',
            'customer_id' => $cust3->id,
            'employee_id' => $emp3->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '14:30:00',
            'end_time' => '15:45:00',
            'status' => 'PENDING',
            'total_amount' => 900.00,
            'notes' => 'Gel manicure & pedicure',
            'created_by' => $userStaff->id,
        ]);
        AppointmentService::create([
            'appointment_id' => $apt3->id,
            'service_id' => $s7->id,
            'price_at_booking' => 900.00,
        ]);

        // 11. Sales
        $sale1 = Sale::create([
            'invoice_code' => 'INV-'.date('Ymd').'-001',
            'customer_id' => $cust1->id,
            'employee_id' => $emp2->id,
            'payment_method' => 'CASH',
            'total_amount' => 1500.00,
            'discount_amount' => 0.00,
            'final_amount' => 1500.00,
            'status' => 'COMPLETED',
        ]);
        SaleItem::create([
            'sale_id' => $sale1->id,
            'item_type' => 'SERVICE',
            'item_id' => $s3->id,
            'item_name' => $s3->service_name,
            'quantity' => 1,
            'unit_price' => 1500.00,
            'subtotal' => 1500.00,
        ]);

        // 12. Expenses
        Expense::create([
            'expense_category' => 'Utilities',
            'description' => 'Meralco Electric Bill (Salon stations & A/C)',
            'amount' => 6450.00,
            'expense_date' => now()->toDateString(),
            'recorded_by' => $userOwner->id,
        ]);
        Expense::create([
            'expense_category' => 'Salon Supplies',
            'description' => 'Towels, gloves, and sanitizing alcohol',
            'amount' => 1250.00,
            'expense_date' => now()->toDateString(),
            'recorded_by' => $userOwner->id,
        ]);

        // 13. Notifications
        Notification::create([
            'title' => 'Low Stock Warning',
            'message' => 'Nail Polish Remover 500ml has only 2 bottles remaining (Minimum: 3).',
            'type' => 'INVENTORY',
            'is_read' => false,
        ]);
        Notification::create([
            'title' => 'Loyalty Reward Earned',
            'message' => 'Customer Ana Maria Gonzales completed 5 visits and earned a 10% discount reward!',
            'type' => 'LOYALTY',
            'is_read' => false,
        ]);

        // 14. Audit Log
        AuditLog::log($userOwner->id, 'SYSTEM_INIT', 'SYSTEM', 'Purita Salon Management System initialized with default seed data.');
    }
}
