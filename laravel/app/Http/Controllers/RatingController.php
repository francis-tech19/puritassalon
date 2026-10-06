<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Service;
use App\Models\ServiceRating;
use App\Models\WebsiteRating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function storeWebsite(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        WebsiteRating::updateOrCreate(['user_id' => Auth::id()], $validated);
        AuditLog::log(Auth::id(), 'WEBSITE_RATING', 'RATINGS', 'Customer submitted website feedback.');

        return back()->with('success', 'Thank you for rating the F Salon website.');
    }

    public function storeService(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_id' => 'required|integer|exists:appointments,id',
            'service_id' => 'required|integer|exists:services,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $appointment = Appointment::whereKey($validated['appointment_id'])
            ->where('customer_id', Auth::user()->customer_id)
            ->where('status', 'COMPLETED')
            ->where('no_show', false)
            ->whereHas('services', fn ($query) => $query->whereKey($validated['service_id']))
            ->first();

        if (! $appointment) {
            return back()->withErrors(['rating' => 'You can only rate a service from your own completed appointment.']);
        }

        ServiceRating::updateOrCreate(
            [
                'customer_id' => Auth::user()->customer_id,
                'service_id' => $validated['service_id'],
                'appointment_id' => $appointment->id,
            ],
            ['rating' => $validated['rating'], 'comment' => $validated['comment'] ?? null],
        );
        AuditLog::log(Auth::id(), 'SERVICE_RATING', 'RATINGS', "Customer rated service #{$validated['service_id']}.");

        return back()->with('success', 'Thank you for rating the service.');
    }

    public function serviceRatings()
    {
        $ratings = ServiceRating::with(['customer', 'service', 'appointment'])->latest()->get();
        $serviceSummaries = Service::withCount('ratings')->withAvg('ratings', 'rating')->orderBy('service_name')->get();

        return view('ratings.services', compact('ratings', 'serviceSummaries'));
    }

    public function websiteRatings()
    {
        $ratings = WebsiteRating::with(['user.customer'])->latest()->get();
        $averageRating = round((float) $ratings->avg('rating'), 1);

        return view('ratings.website', compact('ratings', 'averageRating'));
    }
}