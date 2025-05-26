<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // Menampilkan semua transaksi dengan itemnya
    public function index()
    {
        $transactions = Transaction::with('items')->get();

        return response()->json([
            'message' => 'Success',
            'data' => $transactions
        ]);
    }

    // Menyimpan transaksi baru beserta item-itemnya
    public function store(Request $request)
    {
        $request->validate([
            'user_id'         => 'required|exists:users,id',
            'payment_method'  => 'required|string',
            'items'           => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Hitung total
            $total = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            // Simpan transaksi
            $transaction = Transaction::create([
                'user_id' => $request->user_id,
                'payment_method' => $request->payment_method,
                'total_amount' => $total
            ]);

            // Simpan item-itemnya
            foreach ($request->items as $item) {
                $transaction->items()->create($item);
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaction created successfully',
                'data' => $transaction->load('items')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Transaction failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Menampilkan satu transaksi berdasarkan ID
    public function show($id)
    {
        $transaction = Transaction::with('items')->find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json([
            'message' => 'Success',
            'data' => $transaction
        ]);
    }

    // Menghapus transaksi
    public function destroy($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted']);
    }
}
