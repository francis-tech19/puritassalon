<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\AuditLog;
use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentAvailability;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeStaffOperation();

        $date = $request->input('date', Carbon::today()->toDateString());
        $status = $request->input('status');
        $employeeId = $request->input('employee_id');

        $query = Appointment::with(['customer', 'employee', 'services', 'arrivalMarker'])
            ->whereDate('appointment_date', $date);

        /** @var User|null $user */
        $user = Auth::user();

        if ($status && $status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($employeeId && $employeeId !== 'ALL') {
            $query->where('employee_id', $employeeId);
        }

        $appointments = $query->orderBy('start_time')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $appointments]);
        }

        $customers = Customer::where('status', 'ACTIVE')->orderBy('full_name')->get();
        $employees = Employee::where('status', 'ACTIVE')->orderBy('full_name')->get();
        $services = Service::where('status', 'ACTIVE')->orderBy('service_name')->get();

        return view('appointments.index', compact(
            'appointments',
            'customers',
            'employees',
            'services',
            'date',
            'status',
            'employeeId'
        ));
    }

    public function store(Request $request, AppointmentAvailability $availability): RedirectResponse|JsonResponse
    {
        $this->authorizeStaffOperation();

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'employee_id' => 'required|exists:employees,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'required|integer|exists:services,id',
            'notes' => 'nullable|string',
        ]);

        $services = Service::whereIn('id', $validated['service_ids'])->where('status', 'ACTIVE')->get();
        if ($services->count() !== count(array_unique($validated['service_ids']))) {
            return back()->withErrors(['service_ids' => 'Choose only available services.'])->withInput();
        }
        $appointment = DB::transaction(function () use ($validated, $services, $availability): Appointment|array {
            $employee = Employee::whereKey($validated['employee_id'])->lockForUpdate()->firstOrFail();
            $startTime = Carbon::parse($validated['appointment_date'].' '.$validated['start_time']);
            $endTime = $startTime->copy()->addMinutes($services->sum('duration_minutes'));
            $availabilityError = $availability->error($employee, $services->first(), $validated['appointment_date'], $startTime, $endTime);
            if ($availabilityError) {
                return ['error' => $availabilityError];
            }

            $code = 'APT-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
            $appointment = Appointment::create([
                'appointment_code' => $code,
                'customer_id' => $validated['customer_id'],
                'employee_id' => $employee->id,
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'status' => 'CONFIRMED',
                'arrival_status' => 'NOT_ARRIVED',
                'total_amount' => $services->sum('price'),
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($services as $service) {
                AppointmentService::create([
                    'appointment_id' => $appointment->id,
                    'service_id' => $service->id,
                    'price_at_booking' => $service->price,
                ]);
            }

            return $appointment;
        });

        if (is_array($appointment)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $appointment['error']], 409);
            }

            return back()->withErrors(['start_time' => $appointment['error']])->withInput();
        }

        $this->notifyCustomer($appointment, 'Appointment confirmed', 'The salon confirmed your appointment.');
        $this->notifyStaffForAppointment($appointment);
        AuditLog::log(Auth::id(), 'CREATE', 'APPOINTMENTS', "Created appointment {$appointment->appointment_code} for Customer #{$validated['customer_id']}");

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Appointment confirmed.', 'data' => $appointment->load('customer', 'employee', 'services')], 201);
        }

        return redirect()->route('appointments.index', ['date' => $validated['appointment_date']])
            ->with('success', 'Appointment scheduled successfully!');
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse|JsonResponse
    {
        $this->authorizeStaffOperation();
        $this->authorizeAssignedEmployee($appointment);

        $validated = $request->validate([
            'status' => 'required|in:PENDING,CONFIRMED,DECLINED,COMPLETED,CANCELLED',
            'no_show' => 'nullable|boolean',
            'decline_reason' => 'nullable|string|max:1000',
        ]);

        if ($validated['status'] === 'DECLINED' && empty($validated['decline_reason'])) {
            return back()->withErrors(['decline_reason' => 'A reason is required when declining a reservation.']);
        }
        if ($validated['status'] === 'COMPLETED' && ! in_array($appointment->arrival_status, ['ARRIVED', 'IN_SERVICE'], true)) {
            return back()->withErrors(['status' => 'Mark the customer arrived and start service before completing this appointment.']);
        }
        if (($validated['no_show'] ?? false) && ($appointment->arrival_status ?? 'NOT_ARRIVED') !== 'NOT_ARRIVED') {
            return back()->withErrors(['no_show' => 'Only a customer who has not arrived can be marked as a no-show.']);
        }
        if (($validated['no_show'] ?? false) && ! $this->lateThresholdElapsed($appointment)) {
            return back()->withErrors(['no_show' => 'Wait until the configured late threshold has passed before marking a no-show.']);
        }

        $oldStatus = $appointment->status;
        $noShow = (bool) ($validated['no_show'] ?? false);
        $appointment->update([
            'status' => $validated['status'],
            'no_show' => $noShow,
            'arrival_status' => $noShow ? 'NO_SHOW' : (match ($validated['status']) {
                'COMPLETED' => 'COMPLETED',
                'CANCELLED', 'DECLINED' => 'CANCELLED',
                default => $appointment->arrival_status ?: 'NOT_ARRIVED',
            }),
            'decline_reason' => $validated['status'] === 'DECLINED' ? $validated['decline_reason'] : null,
            'responded_by' => in_array($validated['status'], ['CONFIRMED', 'DECLINED'], true) ? Auth::id() : $appointment->responded_by,
            'responded_at' => in_array($validated['status'], ['CONFIRMED', 'DECLINED'], true) ? now() : $appointment->responded_at,
        ]);
        $this->notifyCustomer($appointment, $validated['status'] === 'DECLINED' ? 'Appointment declined' : 'Appointment confirmed', $validated['status'] === 'DECLINED' ? 'The salon declined your appointment request.' : 'The salon confirmed your appointment.');

        AuditLog::log(Auth::id(), 'STATUS_CHANGE', 'APPOINTMENTS', "Updated appointment {$appointment->appointment_code} status from {$oldStatus} to ".($noShow ? 'NO_SHOW' : $validated['status']));

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
        }

        return back()->with('success', $noShow ? 'Appointment marked as NO SHOW.' : "Appointment marked as {$validated['status']}.");
    }

    public function reschedule(Request $request, Appointment $appointment, AppointmentAvailability $availability): RedirectResponse
    {
        $this->authorizeStaffOperation();
        $this->authorizeAssignedEmployee($appointment);

        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'employee_id' => 'required|exists:employees,id',
        ]);

        $result = DB::transaction(function () use ($appointment, $validated, $availability): Appointment|array {
            $lockedAppointment = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $employee = Employee::whereKey($validated['employee_id'])->lockForUpdate()->firstOrFail();
            $service = $lockedAppointment->services()->firstOrFail();
            $duration = Carbon::parse($lockedAppointment->appointment_date.' '.$lockedAppointment->start_time)
                ->diffInMinutes(Carbon::parse($lockedAppointment->appointment_date.' '.$lockedAppointment->end_time));
            $startTime = Carbon::parse($validated['appointment_date'].' '.$validated['start_time']);
            $endTime = $startTime->copy()->addMinutes($duration);
            $availabilityError = $availability->error($employee, $service, $validated['appointment_date'], $startTime, $endTime, $lockedAppointment->id);
            if ($availabilityError) {
                return ['error' => $availabilityError];
            }

            $lockedAppointment->update([
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'employee_id' => $employee->id,
            ]);

            return $lockedAppointment;
        });
        if (is_array($result)) {
            return back()->withErrors(['start_time' => $result['error']])->withInput();
        }
        $appointment = $result;
        $this->notifyCustomer($appointment, 'Appointment rescheduled', 'The salon rescheduled your appointment to '.$appointment->appointment_date.' at '.date('g:i A', strtotime($appointment->start_time)).'.');
        $this->notifyStaffForAppointment($appointment, 'Appointment rescheduled', 'rescheduled');

        AuditLog::log(Auth::id(), 'RESCHEDULE', 'APPOINTMENTS', "Rescheduled appointment {$appointment->appointment_code}.");

        return back()->with('success', 'Appointment rescheduled successfully.');
    }

    public function updateArrivalStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAssignedEmployee($appointment);
        $validated = $request->validate(['arrival_status' => 'required|in:ARRIVED,IN_SERVICE,COMPLETED,NO_SHOW']);
        $arrivalStatus = $validated['arrival_status'];
        $currentArrivalStatus = $appointment->arrival_status ?? 'NOT_ARRIVED';
        $transitionAllowed = match ($arrivalStatus) {
            'ARRIVED', 'NO_SHOW' => $currentArrivalStatus === 'NOT_ARRIVED',
            'IN_SERVICE' => $currentArrivalStatus === 'ARRIVED',
            'COMPLETED' => in_array($currentArrivalStatus, ['ARRIVED', 'IN_SERVICE'], true),
        };
        if (! $transitionAllowed) {
            return back()->withErrors(['arrival_status' => 'That arrival status transition is not allowed.']);
        }
        if ($arrivalStatus === 'NO_SHOW' && ! $this->lateThresholdElapsed($appointment)) {
            return back()->withErrors(['arrival_status' => 'Wait until the configured late threshold has passed before marking a no-show.']);
        }

        $appointment->update([
            'arrival_status' => $arrivalStatus,
            'arrival_time' => $arrivalStatus === 'ARRIVED' ? ($appointment->arrival_time ?? now()) : $appointment->arrival_time,
            'arrived_marked_by' => $arrivalStatus === 'ARRIVED' ? Auth::id() : $appointment->arrived_marked_by,
            'status' => match ($arrivalStatus) {
                'COMPLETED' => 'COMPLETED',
                'NO_SHOW' => 'CANCELLED',
                default => $appointment->status,
            },
            'no_show' => $arrivalStatus === 'NO_SHOW',
        ]);

        AuditLog::log(Auth::id(), 'ARRIVAL_STATUS', 'APPOINTMENTS', "Updated {$appointment->appointment_code} arrival status to {$arrivalStatus}");

        return back()->with('success', 'Appointment arrival status updated.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorizeStaffOperation();
        $this->authorizeAssignedEmployee($appointment);

        AuditLog::log(Auth::id(), 'DELETE', 'APPOINTMENTS', "Deleted appointment {$appointment->appointment_code}");
        $appointment->delete();

        return back()->with('success', 'Appointment deleted successfully.');
    }

    private function authorizeStaffOperation(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user?->isOwnerOrAdmin() || $user?->isStaff(), 403);
    }

    private function authorizeAssignedEmployee(Appointment $appointment): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user?->isOwnerOrAdmin() || $user?->isStaff(), 403);

        if ($user->isStaff() && $user->employee_id && $appointment->employee_id && (int) $user->employee_id !== (int) $appointment->employee_id) {
            abort(403);
        }
    }

    private function lateThresholdElapsed(Appointment $appointment): bool
    {
        $thresholdMinutes = (int) (BusinessSetting::value('late_threshold_minutes') ?? 10);
        $lateAt = Carbon::parse($appointment->appointment_date.' '.$appointment->start_time)->addMinutes($thresholdMinutes);

        return now()->greaterThanOrEqualTo($lateAt);
    }

    private function notifyCustomer(Appointment $appointment, string $title, string $message): void
    {
        $user = User::where('customer_id', $appointment->customer_id)->first();
        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'appointment_id' => $appointment->id,
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
