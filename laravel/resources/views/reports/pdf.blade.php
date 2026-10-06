<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>F Salon Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 10px; }
        h1 { color: #7A1C49; margin-bottom: 4px; }
        h2 { color: #7A1C49; margin-top: 18px; }
        .period { color: #666; margin-bottom: 16px; }
        .summary { width: 100%; margin-bottom: 18px; }
        .summary td { border: 1px solid #ddd; padding: 8px; width: 33%; }
        .label { color: #666; font-size: 9px; text-transform: uppercase; }
        .amount { font-size: 15px; font-weight: bold; margin-top: 4px; }
        table.report { width: 100%; border-collapse: collapse; }
        .report th { background: #7A1C49; color: #fff; text-align: left; }
        .report th, .report td { border: 1px solid #ddd; padding: 6px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>F Salon Financial and Salon Report</h1>
    <div class="period">Report period: {{ $startDate }} to {{ $endDate }}</div>

    <table class="summary">
        <tr>
            <td><div class="label">Total Revenue</div><div class="amount">PHP {{ number_format($totalRevenue, 2) }}</div></td>
            <td><div class="label">Total Expenses</div><div class="amount">PHP {{ number_format($totalExpenses, 2) }}</div></td>
            <td><div class="label">Net Income</div><div class="amount">PHP {{ number_format($netIncome, 2) }}</div></td>
        </tr>
    </table>

    <h2>Sales Transactions</h2>
    <table class="report">
        <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Employee</th><th>Payment Method</th><th class="right">Amount</th></tr></thead>
        <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice_code }}</td>
                    <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $sale->customer->full_name ?? 'Walk-in' }}</td>
                    <td>{{ $sale->employee->full_name ?? 'Salon Attendant' }}</td>
                    <td>{{ $sale->payment_method }}</td>
                    <td class="right">PHP {{ number_format($sale->final_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No sales in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>