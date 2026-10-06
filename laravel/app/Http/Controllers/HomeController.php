<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->isOwner()) {
                return redirect()->route('dashboard');
            }

            if ($user->isCustomer()) {
                return redirect()->route('customer.dashboard');
            }

            return redirect()->route('appointments.index');
        }

        $settings = Schema::hasTable('business_settings')
            ? BusinessSetting::query()->first()
            : null;
        $featuredServiceNames = [
            'Haircut & Blowdry (Women)',
            'Full Hair Coloring',
            'Keratin Hair Rebonding',
            'Classic Manicure',
            'Facial Deep Cleansing',
            'Classic Pedicure',
        ];

        $services = Schema::hasTable('services')
            ? Service::query()
                ->where('status', 'ACTIVE')
                ->whereIn('service_name', $featuredServiceNames)
                ->get()
                ->sortBy(fn (Service $service) => array_search($service->service_name, $featuredServiceNames, true))
                ->values()
            : collect();
        $section = $request->query('section', 'home');
        $activeSection = in_array($section, ['home', 'services', 'about', 'contact'], true) ? $section : 'home';

        return view('home', compact('settings', 'services', 'activeSection'));
    }
}
