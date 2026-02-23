<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    public function index()
    {
        $items = Category::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $items,
            'errors' => (object)[],
        ]);
    }

    public function show(string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
                'data' => null,
                'errors' => (object)[],
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $category,
            'errors' => (object)[],
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $payload = $request->validated();
        $slug = $payload['slug'] ?? Str::slug($payload['name']);
        $category = Category::create([
            'name' => $payload['name'],
            'slug' => $slug,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Created',
            'data' => $category,
            'errors' => (object)[],
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
                'data' => null,
                'errors' => (object)[],
            ], 404);
        }
        $payload = $request->validated();
        if (isset($payload['name'])) {
            $category->name = $payload['name'];
        }
        if (array_key_exists('slug', $payload)) {
            $category->slug = $payload['slug'] ?: Str::slug($category->name);
        }
        $category->save();
        return response()->json([
            'success' => true,
            'message' => 'Updated',
            'data' => $category,
            'errors' => (object)[],
        ]);
    }

    public function destroy(string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
                'data' => null,
                'errors' => (object)[],
            ], 404);
        }
        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'Deleted',
            'data' => null,
            'errors' => (object)[],
        ]);
    }
}
