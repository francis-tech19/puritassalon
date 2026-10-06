<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Expense;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return view('reports.index', $this->reportData($request));
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        $sales = Sale::with(['customer', 'employee'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderBy('created_at')
            ->get();

        return response()->streamDownload(function () use ($sales): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice', 'Date', 'Customer', 'Employee', 'Payment Method', 'Amount']);
            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->invoice_code,
                    $sale->created_at->format('Y-m-d H:i:s'),
                    $sale->customer->full_name ?? 'Walk-in',
                    $sale->employee->full_name ?? 'Salon Attendant',
                    $sale->payment_method,
                    number_format($sale->final_amount, 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, 'fsalon-sales-'.$startDate.'-to-'.$endDate.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->reportData($request);

        return Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('fsalon-report-'.$data['startDate'].'-to-'.$data['endDate'].'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $data = $this->reportData($request);

        return response()->streamDownload(function () use ($data): void {
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>';
            echo '<h1>F Salon Financial and Salon Report</h1>';
            echo '<p>Report period: '.e($data['startDate']).' to '.e($data['endDate']).'</p>';
            echo '<table border="1"><tr><th>Summary</th><th>Amount</th></tr>';
            echo '<tr><td>Total Revenue</td><td>'.number_format($data['totalRevenue'], 2, '.', '').'</td></tr>';
            echo '<tr><td>Total Expenses</td><td>'.number_format($data['totalExpenses'], 2, '.', '').'</td></tr>';
            echo '<tr><td>Net Income</td><td>'.number_format($data['netIncome'], 2, '.', '').'</td></tr></table><br>';
            echo '<table border="1"><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Employee</th><th>Payment Method</th><th>Amount</th></tr>';
            foreach ($data['sales'] as $sale) {
                echo '<tr><td>'.e($sale->invoice_code).'</td><td>'.e($sale->created_at->format('Y-m-d H:i:s')).'</td><td>'.e($sale->customer->full_name ?? 'Walk-in').'</td><td>'.e($sale->employee->full_name ?? 'Salon Attendant').'</td><td>'.e($sale->payment_method).'</td><td>'.number_format($sale->final_amount, 2, '.', '').'</td></tr>';
            }
            echo '</table></body></html>';
        }, 'fsalon-report-'.$data['startDate'].'-to-'.$data['endDate'].'.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    private function reportData(Request $request): array
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        $sales = Sale::with(['customer', 'employee', 'items'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderBy('created_at', 'desc')->get();
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->orderBy('expense_date', 'desc')->get();
        $appointments = Appointment::with(['customer', 'employee'])->whereBetween('appointment_date', [$startDate, $endDate])->get();
        $totalRevenue = $sales->sum('final_amount');
        $totalExpenses = $expenses->sum('amount');

        return compact('startDate', 'endDate', 'sales', 'expenses', 'appointments', 'totalRevenue', 'totalExpenses') + [
            'netIncome' => $totalRevenue - $totalExpenses,
        ];
    }
}
