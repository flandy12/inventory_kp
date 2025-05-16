<?php

namespace App\Http\Controllers\Api\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Models\Checkout;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;


class CheckoutController extends Controller
{
    public function store(CheckoutRequest $request)
    {
        DB::beginTransaction();

        try {
            // Hitung total harga
            $totalPrice = collect($request->items)->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            // Simpan checkout
            $checkout = Checkout::create([
                'user_id' => auth()->id(),
                'total_price' => $totalPrice,
                'payment_status' => 'paid', // asumsi langsung dibayar
                'payment_method' => $request->payment_method ?? 'cash',
            ]);

            // Loop tiap item
            foreach ($request->items as $item) {
                // Validasi stok produk
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok produk '{$product->name}' tidak mencukupi.");
                }

                // Kurangi stok produk
                $product->decrement('stock', $item['quantity']);

                // Simpan item checkout
                $checkout->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                ]);

                // Simpan log stock out
                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'type'       => 'out',
                    'quantity'   => $item['quantity'],
                    'note'       => 'Checkout ID #' . $checkout->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Checkout berhasil',
                'data'    => $checkout->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Checkout gagal',
                'error'   => $e->getMessage()
            ], 422);
        }
    }
}
