<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiAssistantController extends Controller
{
    public function index()
    {
        return view('ai.index');
    }

    public function ask(Request $request)
    {
        $prompt = $request->input('prompt', 'What is our current salon performance and what improvements do you recommend?');

        // Gather aggregated metrics
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $salesToday = Sale::whereDate('created_at', $today)->sum('final_amount');
        $salesThisMonth = Sale::where('created_at', '>=', $monthStart)->sum('final_amount');
        $appointmentsToday = Appointment::whereDate('appointment_date', $today)
            ->where('status', '!=', 'CANCELLED')
            ->count();
        $totalRevenue = Sale::where('created_at', '>=', $thirtyDaysAgo)->sum('final_amount');
        $totalExpenses = Expense::where('expense_date', '>=', $thirtyDaysAgo->toDateString())->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;

        $criticalStockItems = Inventory::whereBetween('quantity', [1, 3])->pluck('item_name')->toArray();
        $lowStockItems = Inventory::whereBetween('quantity', [1, 8])->pluck('item_name')->toArray();
        $services = Service::where('status', 'ACTIVE')->orderBy('category')->get(['service_name', 'category', 'price', 'duration_minutes', 'description']);
        $workingEmployees = Employee::where('status', 'ACTIVE')->get(['full_name', 'position', 'schedule_notes']);
        $todaySchedule = Appointment::with(['customer:id,full_name', 'employee:id,full_name', 'services:id,service_name'])
            ->whereDate('appointment_date', $today)
            ->where('status', '!=', 'CANCELLED')
            ->orderBy('start_time')->get(['appointment_code', 'customer_id', 'employee_id', 'start_time', 'end_time', 'status']);

        $systemPrompt = "You are a senior salon business consultant assisting the owner of Purita's Beauty Lounge.\n"
            ."Provide a concise, encouraging, senior-friendly business recommendation.\n"
            ."Use bullet points and clear, easy-to-read headers without overly technical jargon.\n\n"
            ."AGGREGATED METRICS (PAST 30 DAYS):\n"
            .'- Total Monthly Revenue: ₱'.number_format($totalRevenue, 2)."\n"
            .'- Total Expenses: ₱'.number_format($totalExpenses, 2)."\n"
            .'- Estimated Net Profit: ₱'.number_format($netProfit, 2)."\n"
            .'- Sales Today: ₱'.number_format($salesToday, 2)."\n"
            .'- Sales This Month: ₱'.number_format($salesThisMonth, 2)."\n"
            ."- Appointments Today: {$appointmentsToday}\n"
            .'- Critical Stock (1-3 units): '.(! empty($criticalStockItems) ? implode(', ', $criticalStockItems) : 'None')."\n"
            .'- Low Stock (1-8 units): '.(! empty($lowStockItems) ? implode(', ', $lowStockItems) : 'None')."\n";

        $systemPrompt .= "\nLIVE SALON CATALOG AND SCHEDULE DATA:\n"
            .'- Active services: '.$services->map(fn ($service) => $service->service_name.' ('.$service->category.', ₱'.number_format($service->price, 2).', '.$service->duration_minutes.' minutes): '.($service->description ?: 'No description'))->implode('; ')."\n"
            .'- Active staff and schedules: '.$workingEmployees->map(fn ($employee) => $employee->full_name.' ('.$employee->position.'): '.($employee->schedule_notes ?: 'Schedule not specified'))->implode('; ')."\n"
            ."- Today's reservations: ".($todaySchedule->map(fn ($appointment) => $appointment->start_time.'-'.$appointment->end_time.' with '.($appointment->employee->full_name ?? 'Unassigned').' for '.$appointment->services->pluck('service_name')->join(', ').' ['.$appointment->status.']')->implode('; ') ?: 'None')."\n"
            ."Answer questions about services, prices, durations, staff, and today's reservations using these records. Do not invent unavailable services or schedules.\n";

        $apiKey = env('GEMINI_API_KEY', config('services.gemini.key'));

        if ($apiKey) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => "{$systemPrompt}\n\nUser Question: {$prompt}"],
                            ],
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $aiText = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($aiText) {
                        return response()->json([
                            'success' => true,
                            'source' => 'Google Gemini Flash AI',
                            'insight' => $aiText,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Fallback to internal engine
            }
        }

        // Rule-based fallback
        $fallback = "**Salon Business Summary (Past 30 Days)**\n\n"
            .'- **Total Monthly Revenue:** ₱'.number_format($totalRevenue, 2)."\n"
            .'- **Total Expenses:** ₱'.number_format($totalExpenses, 2)."\n"
            .'- **Estimated Net Profit:** ₱'.number_format($netProfit, 2)."\n\n"
            ."**Inventory Alerts:**\n"
            .(! empty($lowStockItems) ? '• ⚠️ '.implode("\n• ⚠️ ", $lowStockItems) : '• All inventory items are well-stocked.')."\n\n"
            ."**Recommendation:**\n"
            .'Continue prioritizing top requested services like Keratin Rebonding and Hair Coloring, and restock low inventory promptly.';

        return response()->json([
            'success' => true,
            'source' => 'Internal Salon Analytics Engine',
            'insight' => $fallback,
        ]);
    }
}
