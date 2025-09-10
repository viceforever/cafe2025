<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CategoryProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }
        $categories = CategoryProduct::all();
        $products = Product::all();
        return view('admin.products.index', compact('products'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }
        $categories = CategoryProduct::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }
        $request->validate([
            'name_product' => 'required|string|max:255|unique:products,name_product',
            'description_product' => 'required|string',
            'price_product' => 'required|numeric|min:0',
            'img_product' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_category' => 'required|exists:category_products,id',
        ],[
            'name_product.unique' => 'Товар с таким названием уже существует.',
        ]);

        $imagePath = $request->file('img_product')->store('products', 'public');

        $product = Product::create([
            'name_product' => $request->name_product,
            'description_product' => $request->description_product,
            'price_product' => $request->price_product,
            'img_product' => $imagePath,
            'id_category' => $request->id_category,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Товар успешно создан');
    }

    public function edit(Product $product,Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }
        $categories = CategoryProduct::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }
        $request->validate([
            'name_product' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($product->id),
            ],
            'description_product' => 'required|string',
            'price_product' => 'required|numeric|min:0',
            'img_product' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_category' => 'required|exists:category_products,id',
        ],[
            'name_product.unique' => 'Товар с таким названием уже существует.',
        ]);

        $data = $request->except('img_product');

        if ($request->hasFile('img_product')) {
            $imagePath = $request->file('img_product')->store('products', 'public');
            $data['img_product'] = $imagePath;
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Товар успешно обновлен');
    }

    public function destroy(Product $product,Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Товар успешно удален');
    }
}