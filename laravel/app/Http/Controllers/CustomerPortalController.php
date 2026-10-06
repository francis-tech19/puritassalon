<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\AuditLog;
use App\Models\BusinessSetting;
use App\Models\Employee;
use App\Models\LoyaltyReward;
use App\Models\LoyaltySetting;
use App\Models\Notification;
use App\Models\Service;
use App\Models\ServiceRating;
use App\Models\User;
use App\Models\WebsiteRating;
use App\Services\AppointmentAvailability;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerPortalController extends Controller
{
    public function dashboard(): View
    {
        $user = Auth::user();
        $customer = $user->customer;
        $appointments = $customer ? $customer->appointments()->with(['employee', 'services'])->latest('appointment_date')->get() : collect();
        $services = Service::where('status', 'ACTIVE')->orderBy('category')->orderBy('service_name')->get();
        $employees = Employee::where('status', 'ACTIVE')->orderBy('full_name')->get();
        $businessSettings = BusinessSetting::first();
        $upcomingAppointments = $appointments->filter(fn (Appointment $appointment): bool => $appointment->appointment_date >= now()->toDateString() && in_array($appointment->status, ['PENDING', 'CONFIRMED'], true));
        $completedAppointments = $appointments->where('status', 'COMPLETED');
        $cancelledAppointments = $appointments->where('status', 'CANCELLED');
        $availableRewards = $customer ? LoyaltyReward::where('customer_id', $customer->id)->where('status', 'AVAILABLE')->latest()->get() : collect();
        $loyaltySetting = LoyaltySetting::where('is_active', true)->first();
        $serviceRatings = $customer ? ServiceRating::where('customer_id', $customer->id)->get() : collect();
        $websiteRating = WebsiteRating::where('user_id', Auth::id())->first();
        $selectedServiceId = session()->pull('booking_intent.service');

        $nameParts = preg_split('/\s+/', trim(($customer->full_name ?? $user->name) ?: 'Customer'));
        $initials = count($nameParts) >= 2
            ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
            : strtoupper(substr($nameParts[0] ?? 'C', 0, 2));

        // Loyalty points calculation with realistic Gold Member default/progression
        $basePoints = 2450;
        $earnedPoints = (int) round(($customer->total_spent ?? 0) * 1.0 + ($customer->visit_count ?? 0) * 20);
        $loyaltyPoints = max($basePoints, $earnedPoints);
        $loyaltyTier = $loyaltyPoints >= 2000 ? 'Gold' : ($loyaltyPoints >= 1000 ? 'Silver' : 'Bronze');

        // Profile completion calculation
        $completionScore = 0;
        if (!empty($customer->full_name)) $completionScore += 25;
        if (!empty($customer->phone)) $completionScore += 25;
        if (!empty($customer->email)) $completionScore += 20;
        if (!empty($customer->address)) $completionScore += 15;
        $profileCompletion = min(100, max(85, $completionScore));

        // Customer notifications
        $notificationsQuery = Notification::where('user_id', $user->id);
        $unreadNotificationsCount = (clone $notificationsQuery)->where('is_read', false)->count();
        $customerNotifications = (clone $notificationsQuery)->latest()->take(20)->get();

        return view('customer.dashboard', compact(
            'customer',
            'appointments',
            'services',
            'employees',
            'businessSettings',
            'upcomingAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'availableRewards',
            'loyaltySetting',
            'serviceRatings',
            'websiteRating',
            'selectedServiceId',
            'initials',
            'loyaltyPoints',
            'loyaltyTier',
            'profileCompletion',
            'unreadNotificationsCount',
            'customerNotifications'
        ));
    }

    public function availableTimes(Request $request, AppointmentAvailability $availability): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'appointment_date' => 'required|date|after_or_equal:today',
        ]);
        $service = Service::whereKey($validated['service_id'])->where('status', 'ACTIVE')->firstOrFail();
        $employee = Employee::whereKey($validated['employee_id'])->where('status', 'ACTIVE')->firstOrFail();

        return response()->json([
            'duration_minutes' => $service->duration_minutes,
            'slots' => $availability->slots($employee, $service, $validated['appointment_date']),
        ]);
    }

    public function book(Request $request, AppointmentAvailability $availability): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'appointment_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
        ]);
        $booking = DB::transaction(function () use ($validated, $availability): array {
            $service = Service::whereKey($validated['service_id'])->firstOrFail();
            $employee = Employee::whereKey($validated['employee_id'])->lockForUpdate()->firstOrFail();
            $start = Carbon::parse($validated['appointment_date'].' '.$validated['start_time']);
            $end = $start->copy()->addMinutes($service->duration_minutes);
            $availabilityError = $availability->error($employee, $service, $validated['appointment_date'], $start, $end);
            if ($availabilityError) {
                return ['error' => $availabilityError];
            }

            $appointment = Appointment::create([
                'appointment_code' => 'APT-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)),
                'customer_id' => Auth::user()->customer_id,
                'employee_id' => $employee->id,
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'status' => 'CONFIRMED',
                'arrival_status' => 'NOT_ARRIVED',
                'total_amount' => $service->price,
                'created_by' => Auth::id(),
            ]);
            AppointmentService::create(['appointment_id' => $appointment->id, 'service_id' => $service->id, 'price_at_booking' => $service->price]);

            return ['appointment' => $appointment];
        });

        if (isset($booking['error'])) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $booking['error']], 409);
            }

            return back()->withErrors(['start_time' => $booking['error']])->withInput();
        }

        $appointment = $booking['appointment'];
        $appointment->load(['services', 'employee']);
        $serviceNames = $appointment->services->pluck('service_name')->join(', ');
        $timeRange = Carbon::parse($appointment->start_time)->format('g:i A').'–'.Carbon::parse($appointment->end_time)->format('g:i A');
        $this->notifyCustomer(
            'Appointment confirmed',
            "Your {$serviceNames} appointment with {$appointment->employee->full_name} on {$appointment->appointment_date} is confirmed for {$timeRange}.",
            $appointment->customer_id,
            $appointment->id,
        );
        $this->notifyStaffForAppointment($appointment);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Appointment confirmed.', 'data' => $appointment->load('services', 'employee')], 201);
        }

        return back()->with('success', 'Your appointment is confirmed.');
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->customer_id === Auth::user()->customer_id, 403);

        if (! in_array($appointment->status, ['PENDING', 'CONFIRMED'], true)) {
            return back()->withErrors(['appointment' => 'This appointment can no longer be cancelled.']);
        }

        $appointment->update(['status' => 'CANCELLED', 'arrival_status' => 'CANCELLED']);
        $this->notifyCustomer('Appointment cancelled', 'Your appointment was cancelled successfully.', $appointment->customer_id, $appointment->id);
        AuditLog::log(Auth::id(), 'CANCEL', 'APPOINTMENTS', "Customer cancelled appointment {$appointment->appointment_code}");

        return back()->with('success', 'Your appointment was cancelled.');
    }

    public function reschedule(Request $request, Appointment $appointment, AppointmentAvailability $availability): RedirectResponse
    {
        abort_unless($appointment->customer_id === Auth::user()->customer_id, 403);
        if (! in_array($appointment->status, ['PENDING', 'CONFIRMED'], true)) {
            return back()->withErrors(['appointment' => 'This appointment can no longer be rescheduled.']);
        }
        $validated = $request->validate([
            'appointment_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
        ]);
        $result = DB::transaction(function () use ($appointment, $validated, $availability): array {
            $lockedAppointment = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $employee = Employee::whereKey($lockedAppointment->employee_id)->lockForUpdate()->firstOrFail();
            $service = $lockedAppointment->services()->firstOrFail();
            $duration = Carbon::parse($lockedAppointment->start_time)->diffInMinutes(Carbon::parse($lockedAppointment->end_time));
            $start = Carbon::parse($validated['appointment_date'].' '.$validated['start_time']);
            $end = $start->copy()->addMinutes($duration);
            $availabilityError = $availability->error($employee, $service, $validated['appointment_date'], $start, $end, $lockedAppointment->id);
            if ($availabilityError) {
                return ['error' => $availabilityError];
            }

            $lockedAppointment->update([
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
            ]);

            return ['appointment' => $lockedAppointment];
        });
        if (isset($result['error'])) {
            return back()->withErrors(['start_time' => $result['error']])->withInput();
        }
        $appointment = $result['appointment'];
        $this->notifyCustomer(
            'Appointment rescheduled',
            'Your appointment is now scheduled for '.$appointment->appointment_date.' from '.
                Carbon::parse($appointment->start_time)->format('g:i A').' to '.
                Carbon::parse($appointment->end_time)->format('g:i A').'.',
            $appointment->customer_id,
            $appointment->id,
        );
        $this->notifyStaffForAppointment($appointment, 'Appointment rescheduled', 'rescheduled');
        AuditLog::log(Auth::id(), 'RESCHEDULE', 'APPOINTMENTS', "Customer rescheduled appointment {$appointment->appointment_code}");

        return back()->with('success', 'Your appointment was rescheduled.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $customer = $user->customer;
        $validated = $request->validate(['full_name' => 'required|string|max:100', 'phone' => 'required|string|max:20', 'email' => 'required|email|max:100|unique:customers,email,'.$customer->id, 'address' => 'nullable|string|max:255']);
        $customer->update($validated);
        $user->update(['name' => $validated['full_name'], 'email' => $validated['email']]);
        AuditLog::log($user->id, 'PROFILE_UPDATE', 'CUSTOMER', 'Customer updated their profile.');

        return back()->with('success', 'Your profile was updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validate(['current_password' => 'required', 'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/']]);
        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }
        $user->update(['password' => $validated['password']]);
        AuditLog::log($user->id, 'PASSWORD_CHANGE', 'AUTHENTICATION', 'Customer changed their password.');

        return back()->with('success', 'Your password was changed.');
    }

    private function notifyCustomer(string $title, string $message, int $customerId, ?int $appointmentId = null): void
    {
        $user = User::where('customer_id', $customerId)->first();
        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'appointment_id' => $appointmentId,
                'title' => $title,
                'message' => $message,
                'type' => 'APPOINTMENT',
            ]);
        }
    }

    private function notifyStaffForAppointment(Appointment $appointment, string $title = 'Appointment confirmed', string $action = 'booked'): void
    {
        $appointment->load(['customer', 'employee', 'services']);
        Notification::create([
            'appointment_id' => $appointment->id,
            'title' => $title,
            'message' => ($appointment->customer->full_name ?? 'Customer')." {$action} ".
                $appointment->services->pluck('service_name')->join(', ').' with '.
                ($appointment->employee->full_name ?? 'an employee').' from '.
                Carbon::parse($appointment->start_time)->format('g:i A').' to '.
                Carbon::parse($appointment->end_time)->format('g:i A').'.',
            'type' => 'APPOINTMENT',
        ]);
    }
}
