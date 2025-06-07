<?php

namespace App\Http\Controllers\Api\StockMovement;

use App\Exports\StockInExport;
use App\Exports\StockOutExport;
use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class StockMovementController extends Controller
{
    // Tampilkan semua stock movement
    public function index()
    {
        $stockmovements = StockMovement::all();

        return response()->json([
            'message' => 'Success',
            'data' => $stockmovements
        ], 200);
    }

    // Tampilkan stock movement dengan type = 'in'
    public function getStockIn()
    {
        $stockin = StockMovement::with('product.category')->where('type', 'in')
        ->get()
        ->map(function ($item) {
            // convert model ke array dulu supaya semua atribut ikut terbawa
            $data = $item->toArray();

            // ubah format waktu
            $data['created_at'] = Carbon::parse($data['created_at'])->format('Y-m-d H:i:s');
            $data['updated_at'] = Carbon::parse($data['updated_at'])->format('Y-m-d H:i:s');

            return $data;
        });
       
        if ($stockin->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data stok masuk.',
                'data' => []
            ], 200); // atau 404 jika kamu ingin tandai sebagai "not found"
        }
    
        return response()->json([
            'message' => 'Success',
            'data' => $stockin
        ], 200);
    }

    // Tampilkan stock movement dengan type = 'out'
    public function getStockOut()
    {
        $stockout = StockMovement::with('product.category')->where('type', 'out')
        ->get()
        ->map(function ($item) {
            // convert model ke array dulu supaya semua atribut ikut terbawa
            $data = $item->toArray();

            // ubah format waktu
            $data['created_at'] = Carbon::parse($data['created_at'])->format('Y-m-d H:i:s');
            $data['updated_at'] = Carbon::parse($data['updated_at'])->format('Y-m-d H:i:s');

            return $data;
        });
        return response()->json([
            'message' => 'Success',
            'data' => $stockout
        ], 200);

    }

    // Tampilkan detail stock movement berdasarkan ID
    public function show($id)
    {
        $stockmovement = StockMovement::find($id);

        if (!$stockmovement) {
            return response()->json([
                'message' => 'StockMovement not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Success',
            'data' => $stockmovement
        ], 200);
    }

    // Simpan stock movement baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:in,out',
            'quantity' => 'required|integer|min:1',
            'product_id' => 'required|integer|exists:products,id',
            // tambahkan validasi lainnya sesuai kebutuhan
        ]);

        $stockmovement = StockMovement::create($validated);

        return response()->json([
            'message' => 'StockMovement created successfully',
            'data' => $stockmovement
        ], 201);
    }

    // Update stock movement berdasarkan ID
    public function update(Request $request, $id)
    {
        $stockmovement = StockMovement::find($id);

        if (!$stockmovement) {
            return response()->json([
                'message' => 'StockMovement not found'
            ], 404);
        }

        $validated = $request->validate([
            'type' => 'sometimes|string|in:in,out',
            'quantity' => 'sometimes|integer|min:1',
            'product_id' => 'sometimes|integer|exists:products,id',
            // validasi lainnya sesuai kebutuhan
        ]);

        $stockmovement->update($validated);

        return response()->json([
            'message' => 'StockMovement updated successfully',
            'data' => $stockmovement
        ], 200);
    }

    // Hapus stock movement berdasarkan ID
    public function destroy($id)
    {
        $stockmovement = StockMovement::find($id);

        if (!$stockmovement) {
            return response()->json([
                'message' => 'StockMovement not found'
            ], 404);
        }

        $stockmovement->delete();

        return response()->json([
            'message' => 'StockMovement deleted successfully'
        ], 200);
    }

    public function exportStockIn()
    {
        return Excel::download(new StockInExport, 'stockin.xlsx');
    }

    public function exportStockOut()
    {
        return Excel::download(new StockOutExport, 'stockout.xlsx');
    }

    public function stockout(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $product = Product::find(decrypt($request->product_id));

        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Not enough stock to perform stock out.'
            ], 400);
        }

        // Kurangi stok produk
        $product->stock -= $request->quantity;
        $product->save();

        // Catat ke tabel stock_movements
        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => $request->quantity,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Stock out successful and recorded.',
            'product' => $product,
        ]);
    }

    public function stockin(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.'
            ], 404);
        }

        // Tambah stok produk
        $product->stock += $request->quantity;
        $product->save();

        // Catat ke tabel stock_movements
        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => $request->quantity,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Stock in successful and recorded.',
            'product' => $product,
        ]);
    }

}
