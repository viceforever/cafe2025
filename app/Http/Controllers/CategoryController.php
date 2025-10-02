<?php

namespace App\Http\Controllers;

use App\Models\CategoryProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }

        $categories = CategoryProduct::withCount('products')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }

        $request->validate([
            'name_category' => 'required|string|max:255|unique:category_products',
        ]);

        CategoryProduct::create($request->all());

        return redirect()->route('admin.categories.index')->with('success', 'Категория успешно создана');
    }

    public function edit(CategoryProduct $category,Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, CategoryProduct $category)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }

        $request->validate([
            'name_category' => 'required|string|max:255|unique:category_products,name_category,' . $category->id,
        ]);

        $category->update($request->all());

        return redirect()->route('admin.categories.index')->with('success', 'Категория успешно обновлена');
    }

    public function destroy(CategoryProduct $category,Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->isAdmin()){
            abort(403,'У вас нет прав доступа к этой странице.');
        }

        $productCount = $category->products()->count();
        
        $category->delete();
        
        $message = $productCount > 0 
            ? "Категория успешно удалена вместе с {$productCount} товар(ами)"
            : 'Категория успешно удалена';
            
        return redirect()->route('admin.categories.index')->with('success', $message);
    }
}
