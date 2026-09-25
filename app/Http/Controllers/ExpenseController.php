<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['booking', 'creator'])->orderBy('expense_date', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $expenses = $query->paginate(20)->withQueryString();
        $totalAmount = Expense::sum('amount');
        $bookings = Booking::orderBy('booking_date', 'desc')->take(30)->get();

        return view('expenses.index', compact('expenses', 'totalAmount', 'bookings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:60',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'booking_id' => 'nullable|exists:bookings,id',
            'notes' => 'nullable|string',
        ]);

        $expense = Expense::create([
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'booking_id' => $validated['booking_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        if ($expense->booking_id) {
            $expense->booking->recalculateTotals();
        }

        AuditLog::log('CREATE_EXPENSE', Expense::class, $expense->id, null, [
            'category' => $expense->category,
            'amount' => $expense->amount,
        ]);

        return back()->with('success', 'Biaya operasional berhasil dicatat.');
    }

    public function destroy(Expense $expense)
    {
        $booking = $expense->booking;
        $id = $expense->id;
        $amount = $expense->amount;

        $expense->delete();

        if ($booking) {
            $booking->recalculateTotals();
        }

        AuditLog::log('DELETE_EXPENSE', Expense::class, $id, ['amount' => $amount]);

        return back()->with('success', 'Biaya operasional berhasil dihapus.');
    }
}
