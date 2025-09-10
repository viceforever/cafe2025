<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\Ingredient;
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
        $ingredients = Ingredient::all();
        return view('admin.products.create', compact('categories', 'ingredients'));
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
            'ingredients' => 'array',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ],[
            'name_product.unique' => 'Товар с таким названием уже существует.',
        ]);

        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingredientData) {
                $ingredient = Ingredient::find($ingredientData['id']);
                if ($ingredient->quantity < $ingredientData['quantity']) {
                    return back()->withErrors([
                        'ingredients' => "Недостаточно ингредиента '{$ingredient->name}'. Доступно: {$ingredient->quantity} {$ingredient->unit}, требуется: {$ingredientData['quantity']} {$ingredient->unit}"
                    ])->withInput();
                }
            }
        }

        $imagePath = $request->file('img_product')->store('products', 'public');

        $product = Product::create([
            'name_product' => $request->name_product,
            'description_product' => $request->description_product,
            'price_product' => $request->price_product,
            'img_product' => $imagePath,
            'id_category' => $request->id_category,
        ]);

        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingredientData) {
                $product->ingredients()->attach($ingredientData['id'], [
                    'quantity_needed' => $ingredientData['quantity']
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Товар успешно создан');
    }

    public function edit(Product $product,Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }
        $categories = CategoryProduct::all();
        $ingredients = Ingredient::all();
        return view('admin.products.edit', compact('product', 'categories', 'ingredients'));
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
            'ingredients' => 'array',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ],[
            'name_product.unique' => 'Товар с таким названием уже существует.',
        ]);

        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingredientData) {
                $ingredient = Ingredient::find($ingredientData['id']);
                if ($ingredient->quantity < $ingredientData['quantity']) {
                    return back()->withErrors([
                        'ingredients' => "Недостаточно ингредиента '{$ingredient->name}'. Доступно: {$ingredient->quantity} {$ingredient->unit}, требуется: {$ingredientData['quantity']} {$ingredient->unit}"
                    ])->withInput();
                }
            }
        }

        $data = $request->except('img_product');

        if ($request->hasFile('img_product')) {
            $imagePath = $request->file('img_product')->store('products', 'public');
            $data['img_product'] = $imagePath;
        }

        $product->update($data);

        $product->ingredients()->detach(); // удаляем старые связи
        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingredientData) {
                $product->ingredients()->attach($ingredientData['id'], [
                    'quantity_needed' => $ingredientData['quantity']
                ]);
            }
        }

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
