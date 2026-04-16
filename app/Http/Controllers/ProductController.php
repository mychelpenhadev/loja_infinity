<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('detalhes', compact('product'));
    }

    public function showLegacy(Request $request)
    {
        $id = $request->query('id');
        if (!$id) return redirect('/');
        return $this->show($id);
    }

    public function apiHandler(Request $request)
    {
        $action = $request->input('action', 'list');
        
        try {
            switch ($action) {
                case 'list':
                    $query = Product::query();
                    $category = $request->input('cat');
                    $search = $request->input('search');
                    $limit = $request->input('limit', 12);
                    
                    if ($category && $category !== 'all') {
                        $query->where('category', 'like', "%$category%");
                    }
                    if ($search) {
                        $query->where(function($q) use ($search) {
                            $q->where('name', 'like', "%$search%")
                              ->orWhere('description', 'like', "%$search%");
                        });
                    }
                    
                    $total = $query->count();
                    $products = $query->orderBy('name', 'asc')->paginate($limit);
                    
                    return response()->json([
                        'products' => $products->items(),
                        'pagination' => [
                            'total' => $total,
                            'page' => $products->currentPage(),
                            'limit' => $limit,
                            'pages' => $products->lastPage()
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);

                case 'get':
                    $id = $request->input('id');
                    return response()->json(Product::findOrFail($id));

                case 'get_batch':
                    $ids = array_filter(explode(',', $request->input('ids', '')));
                    return response()->json(Product::whereIn('id', $ids)->get());

                case 'save':
                    if (!\Illuminate\Support\Facades\Auth::check() || \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
                        return response()->json(['error' => 'Não autorizado'], 403);
                    }
                    $id = $request->input('id');
                    $name = $request->input('name');
                    $price = $request->input('price');
                    $category = $request->input('category');
                    $brand = $request->input('brand', '');
                    $image = $request->input('image');
                    $video = $request->input('video', '');
                    $description = $request->input('description', '');

                    if ($id) {
                        $product = Product::findOrFail($id);
                    } else {
                        $product = new Product();
                        $product->rating = 5.0; // Default rating for new products
                    }

                    if (str_starts_with($image, 'data:image/')) {
                        list($type, $imgData) = explode(';', $image);
                        list(, $imgData) = explode(',', $imgData);
                        $imgData = base64_decode($imgData);
                        $extension = explode('/', $type)[1];
                        if ($extension === 'jpeg') $extension = 'jpg';
                        $filename = 'prod_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        $uploadDir = public_path('uploads/produtos');
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        // Additional check for permissions in hosted environment
                        if (!is_writable($uploadDir)) {
                            @chmod($uploadDir, 0777);
                        }
                        
                        file_put_contents($uploadDir . '/' . $filename, $imgData);
                        
                        if ($product->image) {
                            $this->deleteProductImage($product->image);
                        }
                        $image = 'uploads/produtos/' . $filename;
                    }

                    $product->name = $name;
                    $product->price = (float)$price;
                    $product->original_price = $request->input('original_price') ? (float)$request->input('original_price') : null;
                    $product->discount_percent = $request->input('discount_percent') ? (int)$request->input('discount_percent') : null;
                    $product->category = $category;
                    $product->brand = $brand;
                    if ($image) $product->image = $image;
                    $product->video = $video;
                    $product->description = $description;
                    $product->stock_quantity = (int)$request->input('stock_quantity', 0);
                    $product->sold_quantity = (int)$request->input('sold_quantity', 0);
                    
                    $product->save();
                    
                    return response()->json(["status" => "success", "id" => $product->id]);

                case 'delete':
                    if (!\Illuminate\Support\Facades\Auth::check() || \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
                        return response()->json(['error' => 'Não autorizado'], 403);
                    }
                    $id = $request->input('id');
                    $product = Product::findOrFail($id);
                    if ($product->image) {
                        $this->deleteProductImage($product->image);
                    }
                    $product->delete();
                    return response()->json(["status" => "success"]);

                default:
                    return response()->json(['error' => 'Action not supported yet'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()], 400);
        }
    }

    private function deleteProductImage($imagePath)
    {
        if (!$imagePath) return;

        try {
            // Check if it's a local URL (from this domain)
            $baseUrl = asset('/');
            if (str_starts_with($imagePath, $baseUrl)) {
                $relativePath = str_replace($baseUrl, '', $imagePath);
                $fullPath = public_path($relativePath);
            } else {
                // Assume relative path
                $fullPath = public_path($imagePath);
            }

            if (file_exists($fullPath) && !is_dir($fullPath)) {
                @unlink($fullPath);
            }
        } catch (\Exception $e) {
            // Ignore cleanup errors
        }
    }
}
