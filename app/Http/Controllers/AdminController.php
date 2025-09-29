<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }
        $categories = CategoryProduct::all();
        $products = Product::with('category')->get();
        return view('admin.products.index', compact('products', 'categories'));
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
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ],[
            'name_product.unique' => 'Товар с таким названием уже существует.',
            'img_product.required' => 'Изображение товара обязательно для загрузки',
            'img_product.image' => 'Файл должен быть изображением',
            'img_product.mimes' => 'Изображение должно быть в формате: jpeg, png, jpg, gif',
            'img_product.max' => 'Размер изображения не должен превышать 2MB',
            'ingredients.required' => 'Необходимо добавить хотя бы один ингредиент',
            'ingredients.min' => 'Необходимо добавить хотя бы один ингредиент',
        ]);

        foreach ($request->ingredients as $ingredientData) {
            $ingredient = Ingredient::find($ingredientData['id']);
            if ($ingredient->quantity < $ingredientData['quantity']) {
                return back()->withErrors([
                    'ingredients' => "Недостаточно ингредиента '{$ingredient->name}'. Доступно: {$ingredient->quantity} {$ingredient->unit}, требуется: {$ingredientData['quantity']} {$ingredient->unit}"
                ])->withInput();
            }
        }

        $imagePath = $this->processAndStoreImage($request->file('img_product'));

        $product = Product::create([
            'name_product' => $request->name_product,
            'description_product' => $request->description_product,
            'price_product' => $request->price_product,
            'img_product' => $imagePath,
            'id_category' => $request->id_category,
        ]);

        foreach ($request->ingredients as $ingredientData) {
            $product->ingredients()->attach($ingredientData['id'], [
                'quantity_needed' => $ingredientData['quantity']
            ]);
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
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ],[
            'name_product.unique' => 'Товар с таким названием уже существует.',
            'img_product.image' => 'Файл должен быть изображением',
            'img_product.mimes' => 'Изображение должно быть в формате: jpeg, png, jpg, gif',
            'img_product.max' => 'Размер изображения не должен превышать 2MB',
            'ingredients.required' => 'Необходимо добавить хотя бы один ингредиент',
            'ingredients.min' => 'Необходимо добавить хотя бы один ингредиент',
        ]);

        foreach ($request->ingredients as $ingredientData) {
            $ingredient = Ingredient::find($ingredientData['id']);
            if ($ingredient->quantity < $ingredientData['quantity']) {
                return back()->withErrors([
                    'ingredients' => "Недостаточно ингредиента '{$ingredient->name}'. Доступно: {$ingredient->quantity} {$ingredient->unit}, требуется: {$ingredientData['quantity']} {$ingredient->unit}"
                ])->withInput();
            }
        }

        $data = $request->except('img_product');

        if ($request->hasFile('img_product')) {
            $imagePath = $this->processAndStoreImage($request->file('img_product'));
            $data['img_product'] = $imagePath;
            
            // Delete old image if exists
            if ($product->img_product && Storage::disk('public')->exists($product->img_product)) {
                Storage::disk('public')->delete($product->img_product);
            }
        }

        $product->update($data);

        $product->ingredients()->detach();
        foreach ($request->ingredients as $ingredientData) {
            $product->ingredients()->attach($ingredientData['id'], [
                'quantity_needed' => $ingredientData['quantity']
            ]);
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

    private function processAndStoreImage($uploadedFile)
    {
        // Create unique filename
        $filename = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
        $path = 'products/' . $filename;
        
        // Store the original image directly
        // Laravel will handle the file storage
        Storage::disk('public')->put($path, file_get_contents($uploadedFile->getPathname()));
        
        return $path;
    }
}
