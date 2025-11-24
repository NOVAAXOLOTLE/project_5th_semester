<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('limit', 20);
        $page = (int) $request->query('page', 1);

        $query = Product::query();

        // Optional filters
        if ($q = $request->query('q')) {
            // text search (Mongo text index required)
            $query->whereRaw(['$text' => ['$search' => $q]]);
        }
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $total = $query->count();
        $data = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'page' => $page,
            'limit' => $perPage,
            'total' => $total,
            'data' => $data
        ]);
    }

    // GET /api/products/{id}
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json($product);
    }

    // POST /api/products
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['created_at'] = now();
        $product = Product::create($data);
        return response()->json(['inserted_id' => (string)$product->_id], 201);
    }

    // PUT /api/products/{id}
    public function update(StoreProductRequest $request, $id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['error'=>'Not found'], 404);
        $product->fill($request->validated());
        $product->save();
        return response()->json(['status'=>'ok','updated_id' => (string)$product->_id]);
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['error'=>'Not found'], 404);
        $product->delete();
        return response()->json(['status'=>'deleted','deleted_id' => $id]);
    }
}
