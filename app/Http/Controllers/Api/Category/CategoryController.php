<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::paginate(10);

        return response()->json([
            'success'=> true,
            'data' => CategoryResource::collection($categories)
        ], 200);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());
        return response()->json([
            'success'=> true,
            'data' => new CategoryResource($category)
        ], 201);
    }

    public function show($id): JsonResponse
    {
        try {
            $decryptedId = decrypt($id);
            $category = Category::findOrFail($decryptedId);
            
            return response()->json([
                'success'=> true,
                'data' => $category
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid or tampered ID'], 404);
        }
    }

    public function update(UpdateCategoryRequest $request, $id): JsonResponse
    {
        $category = Category::findOrFail(decrypt($id));
        $category->update($request->validated());
        
        return response()->json([
            'success'=> true,
            'data' => new CategoryResource($category)
        ], 201);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json([
            'success'=> true,
            'data' => []
        ], 201);
    }
}
