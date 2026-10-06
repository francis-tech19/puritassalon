<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BusinessSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = BusinessSetting::firstOrCreate([], [
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

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'salon_name' => 'required|string|max:100',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'weekend_opening_time' => 'required|date_format:H:i',
            'weekend_closing_time' => 'required|date_format:H:i|after:weekend_opening_time',
            'contact_phone' => 'required|string|max:20',
            'contact_phone_secondary' => 'required|string|max:20',
            'contact_email' => 'required|email|max:100',
            'address' => 'required|string|max:255',
            'appointment_reminder_minutes' => 'nullable|array',
            'appointment_reminder_minutes.*' => 'integer|min:0|max:180|distinct',
            'late_threshold_minutes' => 'required|integer|min:1|max:180',
        ]);
        $validated['appointment_reminder_minutes'] = implode(',', $validated['appointment_reminder_minutes'] ?? []);

        $settings = BusinessSetting::first();
        if ($settings) {
            $settings->update($validated);
        } else {
            BusinessSetting::create($validated);
        }

        AuditLog::log(Auth::id(), 'UPDATE', 'SETTINGS', 'Updated salon business hours and profile information.');

        return back()->with('success', 'Salon profile settings updated successfully!');
    }
}
