@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">Salon Profile & Settings</h1>
        <p class="text-gray-600 font-semibold mt-0.5">Configure business contact information, operating hours, and official receipt headers.</p>
    </div>

    <div class="card p-8 border-2 border-gray-200 shadow-md">
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-extrabold text-gray-900 mb-1">Salon Business Name</label>
                <input type="text" name="salon_name" value="{{ old('salon_name', $settings->salon_name) }}" required class="form-input font-bold text-lg text-[#7A1C49]">
            </div>

            <fieldset class="space-y-3">
                <legend class="text-sm font-extrabold text-gray-900">Weekday hours (Monday–Friday)</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Opening</label>
                    <input type="time" name="opening_time" value="{{ old('opening_time', $settings->opening_time) }}" required class="form-input font-bold">
                </div>
                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Closing</label>
                    <input type="time" name="closing_time" value="{{ old('closing_time', $settings->closing_time) }}" required class="form-input font-bold">
                </div>
                </div>
            </fieldset>

            <fieldset class="space-y-3">
                <legend class="text-sm font-extrabold text-gray-900">Weekend hours (Saturday–Sunday)</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Opening</label>
                        <input type="time" name="weekend_opening_time" value="{{ old('weekend_opening_time', $settings->weekend_opening_time ?? '09:00') }}" required class="form-input font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Closing</label>
                        <input type="time" name="weekend_closing_time" value="{{ old('weekend_closing_time', $settings->weekend_closing_time ?? '17:00') }}" required class="form-input font-bold">
                    </div>
                </div>
            </fieldset>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">First Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings->contact_phone) }}" required class="form-input font-bold">
                </div>
                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Second Contact Phone</label>
                    <input type="text" name="contact_phone_secondary" value="{{ old('contact_phone_secondary', $settings->contact_phone_secondary) }}" required class="form-input font-bold">
                </div>
            </div>

            <div>
                <label class="block text-sm font-extrabold text-gray-900 mb-1">Business Email</label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $settings->contact_email) }}" required class="form-input font-bold">
            </div>

            <div>
                <label class="block text-sm font-extrabold text-gray-900 mb-1">Full Physical Salon Address</label>
                <textarea name="address" rows="3" required class="form-textarea font-bold">{{ old('address', $settings->address) }}</textarea>
            </div>

            <fieldset class="space-y-3 border-t border-gray-100 pt-5">
                <legend class="text-base font-extrabold text-gray-900">Appointment reminders</legend>
                <p class="text-xs text-gray-500">Staff alerts are delivered through the existing notification center.</p>
                <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm font-semibold text-gray-700">
                    @php($configuredReminders = old('appointment_reminder_minutes', explode(',', $settings->appointment_reminder_minutes ?? '30,15,0')))
                    @foreach([30 => '30 minutes before', 15 => '15 minutes before', 0 => 'At appointment time'] as $minutes => $label)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="appointment_reminder_minutes[]" value="{{ $minutes }}" @checked(in_array((string) $minutes, $configuredReminders, true))>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <label class="block max-w-xs text-sm font-bold text-gray-700">Late customer threshold (minutes)
                    <input type="number" name="late_threshold_minutes" value="{{ old('late_threshold_minutes', $settings->late_threshold_minutes ?? 10) }}" min="1" max="180" required class="form-input mt-1">
                </label>
            </fieldset>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="btn btn-primary px-8 shadow-md">
                    <i data-lucide="save" class="w-5 h-5"></i> Save Settings
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
