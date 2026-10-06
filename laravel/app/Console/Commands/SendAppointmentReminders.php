<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\BusinessSetting;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Create deduplicated staff notifications for upcoming and late appointments';

    public function handle(): int
    {
        $settings = BusinessSetting::first();
        $configuredReminders = trim($settings?->appointment_reminder_minutes ?? '30,15,0');
        $reminderMinutes = $configuredReminders === '' ? [] : array_values(array_unique(array_filter(
            array_map('intval', explode(',', $configuredReminders)),
            fn (int $minutes): bool => $minutes >= 0,
        )));
        $lateThreshold = max(1, (int) ($settings?->late_threshold_minutes ?? 10));
        $now = now()->startOfMinute();
        $notificationsCreated = 0;

        Appointment::with(['customer', 'employee', 'services'])
            ->whereDate('appointment_date', $now->toDateString())
            ->where('status', 'CONFIRMED')
            ->where('arrival_status', 'NOT_ARRIVED')
            ->get()
            ->each(function (Appointment $appointment) use ($reminderMinutes, $lateThreshold, $now, &$notificationsCreated): void {
                $startsAt = Carbon::parse($appointment->appointment_date.' '.$appointment->start_time)->startOfMinute();
                $minutesUntilStart = (int) floor(($startsAt->timestamp - $now->timestamp) / 60);
                $scheduleKey = $startsAt->format('YmdHi');

                foreach ($reminderMinutes as $minutes) {
                    if ($minutesUntilStart === $minutes) {
                        $title = $minutes === 0 ? 'Customer not yet arrived' : 'Upcoming appointment';
                        $when = $minutes === 0 ? 'now' : "in {$minutes} minutes";
                        if ($this->recordOnce($appointment, "reminder_{$minutes}_{$scheduleKey}", $title, $this->appointmentMessage($appointment, "has an appointment {$when}"))) {
                            $notificationsCreated++;
                        }
                    }
                }

                if ($minutesUntilStart === -$lateThreshold
                    && $this->recordOnce(
                        $appointment,
                        "late_{$lateThreshold}_{$scheduleKey}",
                        'Customer is late',
                        $this->appointmentMessage($appointment, "is {$lateThreshold} minutes late"),
                    )) {
                    $notificationsCreated++;
                }
            });

        $this->info("Created {$notificationsCreated} appointment reminder event(s).");

        return self::SUCCESS;
    }

    private function recordOnce(Appointment $appointment, string $eventKey, string $title, string $message): bool
    {
        return DB::transaction(function () use ($appointment, $eventKey, $title, $message): bool {
            $inserted = DB::table('appointment_notification_events')->insertOrIgnore([
                'appointment_id' => $appointment->id,
                'event_key' => $eventKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($inserted !== 1) {
                return false;
            }

            Notification::create([
                'appointment_id' => $appointment->id,
                'title' => $title,
                'message' => $message,
                'type' => 'APPOINTMENT',
            ]);

            return true;
        });
    }

    private function appointmentMessage(Appointment $appointment, string $timing): string
    {
        $customerName = $appointment->customer?->full_name ?? 'A customer';
        $serviceNames = $appointment->services->pluck('service_name')->join(', ');
        $employeeName = $appointment->employee?->full_name ?? 'Unassigned employee';
        $start = date('g:i A', strtotime($appointment->start_time));
        $end = date('g:i A', strtotime($appointment->end_time));

        return "{$customerName} {$timing}. Service: {$serviceNames}. Employee: {$employeeName}. Time: {$start} - {$end}. Status: NOT ARRIVED.";
    }
}