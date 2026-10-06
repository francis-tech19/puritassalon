<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BusinessSetting;
use App\Models\Employee;
use App\Models\Service;
use Carbon\Carbon;

class AppointmentAvailability
{
    public function error(Employee $employee, Service $service, string $date, Carbon $start, Carbon $end, ?int $ignoreAppointmentId = null): ?string
    {
        if ($employee->status !== 'ACTIVE') {
            return 'The selected employee is not available for appointments.';
        }

        if (! $service->exists || $service->status !== 'ACTIVE' || $service->duration_minutes < 5) {
            return 'The selected service is not available for booking.';
        }

        if ($date < now()->toDateString() || ($date === now()->toDateString() && $start->isPast())) {
            return 'Please choose a future appointment time.';
        }

        $settings = BusinessSetting::first();
        [$openingTime, $closingTime] = $this->businessHours($settings, $date);
        $weekday = strtolower(Carbon::parse($date)->format('l'));
        $workingDays = $employee->working_days;

        if ($start->minute % 15 !== 0) {
            return 'Please choose a time slot on a 15-minute interval.';
        }

        if (($workingDays && ! in_array($weekday, $workingDays, true))
            || $start->format('H:i:s') < max($openingTime, $employee->shift_start_time ?? $openingTime)
            || $end->format('H:i:s') > min($closingTime, $employee->shift_end_time ?? $closingTime)) {
            return 'Please choose a time during the employee\'s working hours.';
        }

        $conflict = Appointment::where('employee_id', $employee->id)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['CANCELLED', 'DECLINED'])
            ->when($ignoreAppointmentId, fn ($query) => $query->whereKeyNot($ignoreAppointmentId))
            ->where('start_time', '<', $end->format('H:i:s'))
            ->where('end_time', '>', $start->format('H:i:s'))
            ->exists();

        return $conflict ? 'This appointment time is no longer available. Please select another available time.' : null;
    }

    /** @return array<int, array{start_time: string, end_time: string, available: bool}> */
    public function slots(Employee $employee, Service $service, string $date): array
    {
        $settings = BusinessSetting::first();
        [$businessOpening, $businessClosing] = $this->businessHours($settings, $date);
        $openingTime = max($businessOpening, $employee->shift_start_time ?? $businessOpening);
        $closingTime = min($businessClosing, $employee->shift_end_time ?? $businessClosing);
        $dateStart = Carbon::parse($date.' '.$openingTime);
        $dateEnd = Carbon::parse($date.' '.$closingTime);
        $firstMinute = (int) ceil($dateStart->minute / 15) * 15;
        $slotStart = $dateStart->copy()->second(0);
        if ($firstMinute === 60) {
            $slotStart->addHour()->minute(0);
        } else {
            $slotStart->minute($firstMinute);
        }

        $slots = [];
        while ($slotStart->copy()->addMinutes($service->duration_minutes)->lessThanOrEqualTo($dateEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes($service->duration_minutes);
            $error = $this->error($employee, $service, $date, $slotStart, $slotEnd);
            $slots[] = [
                'start_time' => $slotStart->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'available' => $error === null,
            ];
            $slotStart->addMinutes(15);
        }

        return $slots;
    }

    /** @return array{0: string, 1: string} */
    private function businessHours(?BusinessSetting $settings, string $date): array
    {
        if (Carbon::parse($date)->isWeekend()) {
            return [
                $settings?->weekend_opening_time ?? '09:00:00',
                $settings?->weekend_closing_time ?? '17:00:00',
            ];
        }

        return [
            $settings?->opening_time ?? '10:00:00',
            $settings?->closing_time ?? '16:00:00',
        ];
    }
}