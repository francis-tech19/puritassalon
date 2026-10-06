<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 403);

        $today = Carbon::today();
        $todayAppointments = Appointment::with(['customer', 'services'])
            ->where('employee_id', $employee->id)
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time')
            ->get();
        $upcomingAppointmentsCount = Appointment::where('employee_id', $employee->id)
            ->whereDate('appointment_date', '>', $today)
            ->whereIn('status', ['PENDING', 'CONFIRMED'])
            ->count();
        $completedToday = $todayAppointments->where('status', 'COMPLETED')->count();
        $pendingCount = $todayAppointments->where('status', 'PENDING')->count();
        $todaySales = Sale::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->sum('final_amount');

        return view('employee.dashboard', compact(
            'employee',
            'todayAppointments',
            'upcomingAppointmentsCount',
            'completedToday',
            'pendingCount',
            'todaySales'
        ));
    }
}
