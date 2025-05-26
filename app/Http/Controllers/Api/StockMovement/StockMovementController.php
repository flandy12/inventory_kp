<?php

namespace App\Http\Controllers\Api\StockMovement;

use App\Exports\StockInExport;
use App\Exports\StockOutExport;
use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
    public function stockin()
    {
        $stockin = StockMovement::where('type', 'in')->get();

        return response()->json([
            'message' => 'Success',
            'data' => $stockin
        ], 200);
    }

    // Tampilkan stock movement dengan type = 'out'
    public function stockout()
    {
        $stockout = StockMovement::where('type', 'out')->get();

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
}
