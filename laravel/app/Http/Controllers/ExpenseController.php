<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $date = Carbon::parse($month.'-01');

        $expenses = Expense::with('user')
            ->whereYear('expense_date', $date->year)
            ->whereMonth('expense_date', $date->month)
            ->orderBy('expense_date', 'desc')
            ->get();

        $totalAmount = $expenses->sum('amount');
        $categoryBreakdown = $expenses->groupBy('expense_category')->map(fn ($group) => $group->sum('amount'));

        return view('expenses.index', compact('expenses', 'month', 'totalAmount', 'categoryBreakdown'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
        ]);

        $expense = Expense::create(array_merge($validated, [
            'recorded_by' => Auth::id(),
        ]));

        AuditLog::log(Auth::id(), 'CREATE', 'EXPENSES', 'Recorded expense ₱'.number_format($expense->amount, 2)." for '{$expense->description}'");

        return back()->with('success', 'Expense recorded successfully!');
    }

    public function destroy(Expense $expense)
    {
        AuditLog::log(Auth::id(), 'DELETE', 'EXPENSES', "Deleted expense #{$expense->id}");
        $expense->delete();

        return back()->with('success', 'Expense record removed.');
    }
}
