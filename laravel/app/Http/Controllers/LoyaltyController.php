<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\LoyaltySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    public function index()
    {
        $setting = LoyaltySetting::firstOrCreate([], [
            'visits_required_for_reward' => 5,
            'reward_description' => '10% Discount on Next Service for 5 Completed Visits',
            'discount_percentage' => 10.00,
            'is_active' => true,
        ]);

        $rewards = LoyaltyReward::with('customer')
            ->orderBy('issued_date', 'desc')
            ->paginate(15);

        $topLoyalCustomers = Customer::where('status', 'ACTIVE')
            ->orderBy('visit_count', 'desc')
            ->take(10)
            ->get();

        return view('loyalty.index', compact('setting', 'rewards', 'topLoyalCustomers'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'visits_required_for_reward' => 'required|integer|min:1|max:50',
            'reward_description' => 'required|string|max:255',
            'discount_percentage' => 'required|numeric|min:1|max:100',
            'is_active' => 'required|boolean',
        ]);

        $setting = LoyaltySetting::first();
        if ($setting) {
            $setting->update($validated);
        } else {
            LoyaltySetting::create($validated);
        }

        AuditLog::log(Auth::id(), 'UPDATE', 'LOYALTY', 'Updated salon loyalty reward program rules.');

        return back()->with('success', 'Loyalty settings updated successfully!');
    }

    public function redeem(Request $request, LoyaltyReward $reward)
    {
        if ($reward->status !== 'AVAILABLE') {
            return back()->withErrors(['error' => 'Reward is not available for redemption.']);
        }

        $reward->update([
            'status' => 'REDEEMED',
            'redeemed_date' => now(),
        ]);

        AuditLog::log(Auth::id(), 'REDEEM_REWARD', 'LOYALTY', "Redeemed loyalty reward for customer {$reward->customer->full_name}");

        return back()->with('success', "Reward redeemed for {$reward->customer->full_name}!");
    }
}
