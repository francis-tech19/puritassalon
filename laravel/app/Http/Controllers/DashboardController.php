<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Sale;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Key Performance Indicators
        $todaySales = Sale::whereDate('created_at', $today)->sum('final_amount');
        $todayAppointmentsCount = Appointment::whereDate('appointment_date', $today)->count();
        $totalCustomers = Customer::where('status', 'ACTIVE')->count();
        $lowStockCount = Inventory::where('quantity', '<=', 8)->count();

        // Today's Appointments
        $todayAppointments = Appointment::with(['customer', 'employee', 'services'])
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time')
            ->take(8)
            ->get();

        // Recent Sales
        $recentSales = Sale::with(['customer', 'employee', 'items'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Low Stock Items
        $lowStockItems = Inventory::where('quantity', '<=', 8)
            ->orderBy('quantity')
            ->take(5)
            ->get();

        // Monthly Summary
        $monthlySales = Sale::whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->sum('final_amount');

        $monthlyExpenses = Expense::whereMonth('expense_date', $today->month)
            ->whereYear('expense_date', $today->year)
            ->sum('amount');

        $netProfit = $monthlySales - $monthlyExpenses;

        return view('dashboard.index', compact(
            'todaySales',
            'todayAppointmentsCount',
            'totalCustomers',
            'lowStockCount',
            'todayAppointments',
            'recentSales',
            'lowStockItems',
            'monthlySales',
            'monthlyExpenses',
            'netProfit'
        ));
    }
}
