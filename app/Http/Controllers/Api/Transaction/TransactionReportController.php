<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionReportController extends Controller
{
    // Report semua transaksi lengkap dengan itemnya
    public function index()
    {
        // Ambil transaksi dengan relasi itemnya
        $transactions = Transaction::with('items')->get();

        return response()->json([
            'message' => 'Success',
            'data' => $transactions,
        ], 200);
    }

    // Report transaksi berdasarkan tanggal range
    public function reportByDate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $transactions = Transaction::with('items')
            ->whereBetween('created_at', [$request->start_date, $request->end_date])
            ->get();

        return response()->json([
            'message' => 'Success',
            'data' => $transactions,
        ], 200);
    }

    // Report ringkasan total transaksi per hari (contoh)
    public function dailySummary()
    {
        $summary = Transaction::selectRaw('DATE(created_at) as date, COUNT(*) as total_transactions, SUM(total_amount) as total_revenue')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'message' => 'Success',
            'data' => $summary,
        ], 200);
    }

    public function exportReport() {
        return Excel::download(new ReportExport, 'report.xlsx');
    }
}
