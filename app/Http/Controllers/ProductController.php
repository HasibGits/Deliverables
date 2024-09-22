<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    function list(){
        return Category::all();
    }

    function manyRel(){
        return category::find(554)->productsManyData;
    }
 
    function manyManyRel(){
        return product::find(2)->productsManyManyData;
    }

    public function index()
    {
        return Product::with(['category', 'attributes'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'attributes' => 'array',
            'attributes.*.id' => 'exists:attributes,id',
            'attributes.*.value' => 'string|max:255',
        ]);

        $product = Product::create($validated);
        if ($request->has('attributes')) {
            foreach ($request->attributes as $attribute) {
                $product->attributes()->attach($attribute['id'], ['value' => $attribute['value']]);
            }
        }

        return response()->json($product->load('category', 'attributes'), 201);
    }

    public function show($id)
    {
        return Product::with(['category', 'attributes'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric',
            'category_id' => 'sometimes|required|exists:categories,id',
            'attributes' => 'array',
            'attributes.*.id' => 'exists:attributes,id',
            'attributes.*.value' => 'string|max:255',
        ]);

        $product->update($validated);
        if ($request->has('attributes')) {
            $product->attributes()->sync([]);
            foreach ($request->attributes as $attribute) {
                $product->attributes()->attach($attribute['id'], ['value' => $attribute['value']]);
            }
        }

        return response()->json($product->load('category', 'attributes'));
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
    
    public function search(Request $request)
    {
    $query = $request->input('query');
    
    $products = Cache::remember('products_search_' . $query, 60, function () use ($query) {
        return Product::where('name', 'like', '%' . $query . '%')->get();
    });

    return view('products.index', compact('products'));
    }

}

