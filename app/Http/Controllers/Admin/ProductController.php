public function store(Request $request)
    {
        $request->validate([
            'name_product' => 'required|string|max:255',
            'description_product' => 'required|string',
            'price_product' => 'required|numeric|min:0',
            'img_product' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_category' => 'required|exists:categories,id',
            'ingredients' => 'array',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ]);

        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingredientData) {
                $ingredient = \App\Models\Ingredient::find($ingredientData['id']);
                if ($ingredient->quantity < $ingredientData['quantity']) {
                    return back()->withErrors([
                        'ingredients' => "Недостаточно ингредиента '{$ingredient->name}'. Доступно: {$ingredient->quantity} {$ingredient->unit}, требуется: {$ingredientData['quantity']} {$ingredient->unit}"
                    ])->withInput();
                }
            }
        }

        // Загрузка изображения
        if ($request->hasFile('img_product')) {
            $imageName = time().'.'.$request->img_product->extension();
            $request->img_product->move(public_path('images'), $imageName);
        }

        $product = Product::create([
            'name_product' => $request->name_product,
            'description_product' => $request->description_product,
            'price_product' => $request->price_product,
            'img_product' => $imageName ?? null,
            'id_category' => $request->id_category,
        ]);

        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingredientData) {
                $product->ingredients()->attach($ingredientData['id'], [
                    'quantity_needed' => $ingredientData['quantity']
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Товар успешно создан');
    }
