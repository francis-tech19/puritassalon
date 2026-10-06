<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::withCount('appointments', 'sales')
            ->orderBy('full_name')
            ->get();

        return view('employees.index', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'position' => 'required|string|max:50',
            'schedule_notes' => 'nullable|string',
            'working_days' => 'nullable|array',
            'working_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'shift_start_time' => 'nullable|date_format:H:i|required_with:shift_end_time',
            'shift_end_time' => 'nullable|date_format:H:i|after:shift_start_time|required_with:shift_start_time',
            'status' => 'required|in:ACTIVE,INACTIVE,ARCHIVED',
        ]);

        $code = 'EMP-'.strtoupper(substr(uniqid(), -4));

        $employee = Employee::create(array_merge($validated, [
            'employee_code' => $code,
        ]));

        AuditLog::log(Auth::id(), 'CREATE', 'EMPLOYEES', "Created employee {$employee->full_name} ({$code})");

        return back()->with('success', "Employee {$employee->full_name} added successfully!");
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'position' => 'required|string|max:50',
            'schedule_notes' => 'nullable|string',
            'working_days' => 'nullable|array',
            'working_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'shift_start_time' => 'nullable|date_format:H:i|required_with:shift_end_time',
            'shift_end_time' => 'nullable|date_format:H:i|after:shift_start_time|required_with:shift_start_time',
            'status' => 'required|in:ACTIVE,INACTIVE,ARCHIVED',
        ]);

        $employee->update($validated);

        AuditLog::log(Auth::id(), 'UPDATE', 'EMPLOYEES', "Updated employee {$employee->full_name}");

        return back()->with('success', 'Employee updated successfully.');
    }

    public function updateSchedule(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'shift_start_time' => 'required|date_format:H:i',
            'shift_end_time' => 'required|date_format:H:i|after:shift_start_time',
        ]);

        $employee->update($validated);
        AuditLog::log(Auth::id(), 'UPDATE_SCHEDULE', 'EMPLOYEES', "Updated working schedule for {$employee->full_name}");

        return back()->with('success', 'Employee working schedule updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        AuditLog::log(Auth::id(), 'DELETE', 'EMPLOYEES', "Archived employee {$employee->full_name}");
        $employee->update(['status' => 'ARCHIVED']);

        return back()->with('success', 'Employee status changed to Archived.');
    }
}
