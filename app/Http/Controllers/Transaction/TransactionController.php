<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\TransactionRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function store(TransactionRequest $request){
        $data = $request->validated();

        $total = collect($data['items'])->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        if ($data['payment_amount'] < $total) {
            return response()->json(['error' => 'Uang tidak cukup.'], 422);
        }

        // Simpan transaksi utama
        $transaction = Transaction::create([
            'user_id'        => $data['user_id'],
            'total'          => $total,
            'payment_amount' => $data['payment_amount'],
            'change'         => $data['payment_amount'] - $total,
        ]);

        // Simpan item transaksi + kurangi stok
        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->stock < $item['quantity']) {
                return response()->json(['error' => 'Stok tidak cukup untuk ' . $product->name], 422);
            }

            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id'     => $item['product_id'],
                'quantity'       => $item['quantity'],
                'price'          => $item['price'],
                'subtotal'       => $item['quantity'] * $item['price'],
            ]);
                
            $product->decrement('stock', $item['quantity']);
        }

        return response()->json(['message' => 'Transaksi berhasil disimpan', 'transaction_id' => $transaction->id]);    
    }
}