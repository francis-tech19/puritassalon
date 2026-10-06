<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $days = 7;
        $salesTrend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $total = Sale::whereDate('created_at', $date)->sum('final_amount');
            $salesTrend[] = [
                'date' => $date->format('M d'),
                'total' => (float) $total,
            ];
        }

        // Top Services Sold
        $topServices = SaleItem::select('item_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->where('item_type', 'SERVICE')
            ->groupBy('item_name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        // Stylist Performance
        $stylistPerformance = Employee::withCount('appointments')
            ->with(['sales' => function ($q) {
                $q->select('employee_id', DB::raw('SUM(final_amount) as total_sales'))->groupBy('employee_id');
            }])
            ->where('status', 'ACTIVE')
            ->get();

        // Payment Methods Distribution
        $paymentMethods = Sale::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(final_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        return view('analytics.index', compact(
            'salesTrend',
            'topServices',
            'stylistPerformance',
            'paymentMethods'
        ));
    }
}
