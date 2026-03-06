<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Models\ProductMaterial;
use App\Models\ProductCareInstruction;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['category', 'images', 'colors', 'sizes', 'materials', 'careInstructions']);

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category = $request->string('category')->toString()) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category)->orWhere('name', $category);
            });
        }

        if ($code = $request->string('code')->toString()) {
            $query->where('code', $code);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if (!is_null($request->input('featured'))) {
            $query->where('featured', filter_var($request->input('featured'), FILTER_VALIDATE_BOOL));
        }

        if ($min = $request->input('min_price')) {
            $query->where('price', '>=', (float) $min);
        }
        if ($max = $request->input('max_price')) {
            $query->where('price', '<=', (float) $max);
        }

        $sort = $request->string('sort')->toString();
        $direction = 'asc';
        $sortable = ['name', 'price', 'created_at'];
        if ($sort) {
            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $sort = ltrim($sort, '-');
            } elseif (str_contains($sort, ':')) {
                [$sort, $direction] = explode(':', $sort, 2);
                $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
            }
            if (in_array($sort, $sortable, true)) {
                $query->orderBy($sort, $direction);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);
        $page = max((int) $request->input('page', 1), 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'items' => $paginator->items(),
                'pagination' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'total_pages' => $paginator->lastPage(),
                ],
            ],
            'errors' => (object)[],
        ]);
    }

    public function show(string $id)
    {
        $product = Product::with(['category', 'images', 'colors', 'sizes', 'materials', 'careInstructions'])->find($id);
        if (!$product) {
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
            'data' => $product,
            'errors' => (object)[],
        ]);
    }

    public function showByCode(string $category, string $code)
    {
        $product = Product::with(['category', 'images', 'colors', 'sizes', 'materials', 'careInstructions'])
            ->where('code', $code)
            ->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category)->orWhere('name', $category);
            })
            ->first();
        if (!$product) {
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
            'data' => $product,
            'errors' => (object)[],
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $payload = $request->validated();

        $product = DB::transaction(function () use ($payload, $request) {
            $product = Product::create([
                'category_id' => $payload['category_id'],
                'code' => $payload['code'],
                'name' => $payload['name'],
                'slug' => $payload['slug'] ?? null,
                'description' => $payload['description'] ?? null,
                'price' => $payload['price'] ?? null,
                'currency' => $payload['currency'] ?? 'LKR',
                'cover_image' => $payload['cover_image'] ?? null,
                'lead_time_days' => $payload['lead_time_days'] ?? null,
                'featured' => $payload['featured'] ?? false,
                'status' => $payload['status'] ?? 'active',
                'seo_title' => $payload['seo_title'] ?? null,
                'seo_description' => $payload['seo_description'] ?? null,
            ]);

            $this->syncChildren($product, $payload);

            if ($request->hasFile('images')) {
                $this->handleUploads($product->id, $request->file('images'));
            }

            return $product;
        });

        return response()->json([
            'success' => true,
            'message' => 'Created',
            'data' => $product->load(['category', 'images', 'colors', 'sizes', 'materials', 'careInstructions']),
            'errors' => (object)[],
        ], 201);
    }

    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
                'data' => null,
                'errors' => (object)[],
            ], 404);
        }

        $payload = $request->validated();

        DB::transaction(function () use ($product, $payload) {
            $product->fill($payload);
            $product->save();
            $this->syncChildren($product, $payload, true);
        });

        return response()->json([
            'success' => true,
            'message' => 'Updated',
            'data' => $product->load(['category', 'images', 'colors', 'sizes', 'materials', 'careInstructions']),
            'errors' => (object)[],
        ]);
    }

    public function destroy(string $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
                'data' => null,
                'errors' => (object)[],
            ], 404);
        }
        $product->delete();
        return response()->json([
            'success' => true,
            'message' => 'Deleted',
            'data' => null,
            'errors' => (object)[],
        ]);
    }

    public function uploadImage(Request $request, string $id)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'alt' => ['nullable', 'string', 'max:150'],
        ]);
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
                'data' => null,
                'errors' => (object)[],
            ], 404);
        }
        $file = $request->file('image');
        $stored = $this->storeImageFile($product->id, $file);
        $sort = ($product->images()->max('sort_order') ?? 0) + 1;
        $image = $product->images()->create([
            'path' => $stored['path'],
            'alt' => $request->string('alt')->toString() ?: null,
            'sort_order' => $sort,
        ]);
        if (!$product->cover_image) {
            $product->cover_image = $stored['path'];
            $product->save();
        }
        return response()->json([
            'success' => true,
            'message' => 'Image uploaded',
            'data' => $image,
            'errors' => (object)[],
        ], 201);
    }

    public function deleteImage(string $id, string $imageId)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
                'data' => null,
                'errors' => (object)[],
            ], 404);
        }
        $image = $product->images()->where('id', $imageId)->first();
        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
                'data' => null,
                'errors' => (object)[],
            ], 404);
        }
        Storage::disk('public')->delete($image->path);
        $image->delete();
        return response()->json([
            'success' => true,
            'message' => 'Image deleted',
            'data' => null,
            'errors' => (object)[],
        ]);
    }

    public function setCover(string $id, string $imageId)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
                'data' => null,
                'errors' => (object)[],
            ], 404);
        }
        $image = $product->images()->where('id', $imageId)->first();
        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
                'data' => null,
                'errors' => (object)[],
            ], 404);
        }
        $product->cover_image = $image->path;
        $product->save();
        return response()->json([
            'success' => true,
            'message' => 'Cover image set',
            'data' => $product->fresh(['images']),
            'errors' => (object)[],
        ]);
    }

    protected function syncChildren(Product $product, array $payload, bool $isUpdate = false): void
    {
        if (isset($payload['colors']) && is_array($payload['colors'])) {
            $product->colors()->delete();
            $i = 0;
            foreach ($payload['colors'] as $row) {
                $product->colors()->create([
                    'name' => $row['name'],
                    'hex' => $row['hex'] ?? null,
                    'sort_order' => $i++,
                ]);
            }
        }
        if (isset($payload['sizes']) && is_array($payload['sizes'])) {
            $product->sizes()->delete();
            $i = 0;
            foreach ($payload['sizes'] as $row) {
                $product->sizes()->create([
                    'name' => $row['name'],
                    'sort_order' => $i++,
                ]);
            }
        }
        if (isset($payload['materials']) && is_array($payload['materials'])) {
            $product->materials()->delete();
            $i = 0;
            foreach ($payload['materials'] as $row) {
                $product->materials()->create([
                    'text' => $row['text'],
                    'sort_order' => $i++,
                ]);
            }
        }
        if (isset($payload['care']) && is_array($payload['care'])) {
            $product->careInstructions()->delete();
            $i = 0;
            foreach ($payload['care'] as $row) {
                $product->careInstructions()->create([
                    'text' => $row['text'],
                    'sort_order' => $i++,
                ]);
            }
        }
    }

    protected function handleUploads(int $productId, array $files): void
    {
        $maxSort = ProductImage::where('product_id', $productId)->max('sort_order') ?? 0;
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $stored = $this->storeImageFile($productId, $file);
                ProductImage::create([
                    'product_id' => $productId,
                    'path' => $stored['path'],
                    'alt' => null,
                    'sort_order' => ++$maxSort,
                ]);
            }
        }
    }

    protected function storeImageFile(int $productId, UploadedFile $file): array
    {
        $path = $file->store("products/{$productId}", 'public');
        return ['path' => $path, 'url' => Storage::disk('public')->url($path)];
    }
}