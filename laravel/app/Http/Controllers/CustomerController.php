<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Customer::with(['loyaltyRewards']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('full_name')->paginate(15)->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $code = 'CUST-'.strtoupper(substr(uniqid(), -4));

        $customer = Customer::create(array_merge($validated, [
            'customer_code' => $code,
            'status' => 'ACTIVE',
        ]));

        AuditLog::log(Auth::id(), 'CREATE', 'CUSTOMERS', "Created customer profile {$customer->full_name} ({$code})");

        return back()->with('success', "Customer {$customer->full_name} registered successfully!");
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:ACTIVE,ARCHIVED',
        ]);

        $customer->update($validated);

        AuditLog::log(Auth::id(), 'UPDATE', 'CUSTOMERS', "Updated customer profile {$customer->full_name}");

        return back()->with('success', 'Customer profile updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        AuditLog::log(Auth::id(), 'DELETE', 'CUSTOMERS', "Deleted customer profile {$customer->full_name}");
        $customer->delete();

        return back()->with('success', 'Customer profile removed.');
    }
}
