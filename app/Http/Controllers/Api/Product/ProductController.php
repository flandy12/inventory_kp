<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() : JsonResponse
    {
        $products = Product::with('category')->paginate(10);
        return response()->json(ProductResource::collection($products));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
    
        // Simpan gambar produk jika ada
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        
        // Simpan produk dulu untuk mendapatkan ID
        $product = Product::create($data);
    
        // Generate URL produk (pastikan route ini ada dan benar)
        $url = route('products.show', ['product' => $product->id]);
    
        // Buat QR Code
        $qrCode = new QrCode($url);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
    
        // Buat folder storage jika belum ada
        $qrFolder = storage_path('app/public/qrcodes');
        if (!file_exists($qrFolder)) {
            mkdir($qrFolder, 0755, true);
        }
    
        // Simpan file QR Code
        $qrFileName = "product_{$product->id}.png";
        $qrPath = "qrcodes/{$qrFileName}";
        $result->saveToFile($qrFolder . '/' . $qrFileName);
    
        // Simpan path QR Code ke kolom barcode
        $product->barcode = $qrPath; // atau gunakan kolom lain misalnya: $product->qr_code_image
        $product->save();
        

        return response()->json(new ProductResource($product), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json(new ProductResource($product));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return response()->json(new ProductResource($product));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse {

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(null, 204);
    }

    public function scanBarcode($barcode)
    {
        $product = Product::where('barcode', $barcode)->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json(new ProductResource($product));
    }

}

