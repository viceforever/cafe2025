<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::paginate(4);
        $lowStockIngredients = Ingredient::whereColumn('quantity', '<=', 'min_quantity')->get();
        
        return view('admin.ingredients.index', compact('ingredients', 'lowStockIngredients'));
    }

    public function create()
    {
        return view('admin.ingredients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'min_quantity' => 'required|numeric|min:0',
        ], [
            'name.unique' => 'Ингредиент с таким названием уже существует',
        ]);

        Ingredient::create($request->all());

        return redirect()->route('admin.ingredients.index')
            ->with('success', 'Ингредиент успешно добавлен');
    }

    public function edit(Ingredient $ingredient)
    {
        return view('admin.ingredients.edit', compact('ingredient'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name,' . $ingredient->id,
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'min_quantity' => 'required|numeric|min:0',
        ], [
            'name.unique' => 'Ингредиент с таким названием уже существует',
        ]);

        $ingredient->update($request->all());

        return redirect()->route('admin.ingredients.index')
            ->with('success', 'Ингредиент обновлен');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return redirect()->route('admin.ingredients.index')
            ->with('success', 'Ингредиент удален');
    }


    public function getAll()
    {
        $ingredients = Ingredient::all();
        return response()->json($ingredients);
    }

    public function updateQuantity(Request $request, Ingredient $ingredient)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0',
        ]);

        $ingredient->update(['quantity' => $request->quantity]);

        return redirect()->route('admin.ingredients.index')
            ->with('success', 'Остаток обновлен');
    }
}
