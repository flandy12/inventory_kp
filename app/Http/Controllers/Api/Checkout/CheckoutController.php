<?php

namespace App\Http\Controllers\Api\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Models\Checkout;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class CheckoutController extends Controller
{
    public function store( CheckoutRequest $request) {

        // Hitung total harga dari item
        $totalPrice = collect($request->items)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });


        $checkout = Checkout::create([
            'user_id' => auth()->id(),
            'total_price' => $totalPrice,
            'payment_status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            $checkout->items()->create([
                'product_id' => $item['product_id'],
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        return response()->json($checkout->load('items'));

    }

}
