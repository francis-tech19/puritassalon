<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::orderBy('category')->orderBy('service_name')->get();
        $categories = Service::select('category')->distinct()->pluck('category');

        return view('services.index', compact('services', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:5|max:720',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        unset($validated['photo']);
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('services', 'public');
        }
        $service = Service::create($validated);

        AuditLog::log(Auth::id(), 'CREATE', 'SERVICES', "Created service {$service->service_name} (₱{$service->price})");

        return back()->with('success', "Service {$service->service_name} added successfully!");
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:5|max:720',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        unset($validated['photo']);
        if ($request->hasFile('photo')) {
            if ($service->photo_path) {
                Storage::disk('public')->delete($service->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('services', 'public');
        }
        $service->update($validated);

        AuditLog::log(Auth::id(), 'UPDATE', 'SERVICES', "Updated service {$service->service_name}");

        return back()->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        AuditLog::log(Auth::id(), 'DELETE', 'SERVICES', "Deleted service {$service->service_name}");
        if ($service->photo_path) {
            Storage::disk('public')->delete($service->photo_path);
        }
        $service->delete();

        return back()->with('success', 'Service removed successfully.');
    }
}
