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
    ], [
        'name_product.required' => 'Название товара обязательно для заполнения',
        'name_product.max' => 'Название товара не должно превышать 255 символов',
        'description_product.required' => 'Описание товара обязательно для заполнения',
        'price_product.required' => 'Цена товара обязательна для заполнения',
        'price_product.numeric' => 'Цена товара должна быть числом',
        'price_product.min' => 'Цена товара не может быть отрицательной',
        'img_product.required' => 'Изображение товара обязательно для загрузки',
        'img_product.image' => 'Файл должен быть изображением',
        'img_product.mimes' => 'Изображение должно быть в формате: jpeg, png, jpg, gif',
        'img_product.max' => 'Размер изображения не должен превышать 2MB',
        'id_category.required' => 'Выберите категорию товара',
        'id_category.exists' => 'Выбранная категория не существует',
        'ingredients.*.id.required' => 'Выберите ингредиент',
        'ingredients.*.id.exists' => 'Выбранный ингредиент не существует',
        'ingredients.*.quantity.required' => 'Укажите количество ингредиента',
        'ingredients.*.quantity.numeric' => 'Количество ингредиента должно быть числом',
        'ingredients.*.quantity.min' => 'Количество ингредиента должно быть больше 0',
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
