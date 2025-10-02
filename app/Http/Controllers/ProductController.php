<?php
namespace App\Http\Controllers;
use App\Models\CategoryProduct;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $categories = CategoryProduct::all(); // все категории
        $products = Product::with('category')->get()->groupBy('id_category');
        $totalProducts = Product::count();
        return view('products.index', compact('categories','products', 'totalProducts'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $categories = CategoryProduct::all(); // все категории
        $totalProducts = Product::count();

        if ($query){
            $products = Product::where('name_product', 'LIKE', "%{$query}%")
            ->orWhere('description_product', 'LIKE', "%{$query}%")
            ->with('category')
            ->get()
            ->groupBy('id_category');
        } else{
            $products = Product::with('category')->get()->groupBy('id_category');
        }
        return view('products.index', compact('products','categories','query', 'totalProducts'));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);  // Нахождение продукта по ID
        return view('products.product', compact('product'));
    }

    public function addToCart(Request $request,$id)
    {
        $product = Product::findOrFail($id);
        $quantity = $request->input('quantity', 1);
        $cart = session()->get('cart', []);
        
        if(isset($cart[$id])) {
            $cart[$id]['quantity']+= $quantity;
        } else {
            $cart[$id] = [
                "id" => $product->id,
                "name" => $product->name_product,
                "quantity" => $quantity,
                "price" => $product->price_product,
                "img_product" => $product->img_product
            ];
        }
        
        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Товар добавлен в корзину!');
    }

    // Удаление товара из корзины
    public function removeFromCart($id)
    {
        $cart = session()->get('cart');

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Товар удалён из корзины!');
    }


    public function cart()
    {
        $cart = session()->get('cart');
        return view('cart.index', compact('cart'));
    }

    public function update(Request $request, $id)
    {
        $action = $request->input('action');
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            if ($action === 'increase') {
                $cart[$id]['quantity']++;
            } elseif ($action === 'decrease') {
                if ($cart[$id]['quantity'] > 1) {
                    $cart[$id]['quantity']--;
                } else {
                    unset($cart[$id]);
                    return response()->json([
                        'success' => true,
                        'removed' => true,
                        'cartTotal' => number_format($this->getCartTotal(), 2, '.', '')
                    ]);
                }
            } else {
                // Если действие не указано, устанавливаем количество напрямую
                $quantity = $request->input('quantity');
                if ($quantity > 0) {
                    $cart[$id]['quantity'] = $quantity;
                } else {
                    unset($cart[$id]);
                    return response()->json([
                        'success' => true,
                        'removed' => true,
                        'cartTotal' => number_format($this->getCartTotal(), 2, '.', '')
                    ]);
                }
            }

            session()->put('cart', $cart);

            $itemTotal = $cart[$id]['price'] * $cart[$id]['quantity'];
            $cartTotal = $this->getCartTotal();

            return response()->json([
                'success' => true,
                'itemTotal' => number_format($itemTotal, 2, '.', ''),
                'cartTotal' => number_format($cartTotal, 2, '.', ''),
                'quantity' => $cart[$id]['quantity']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Товар не найден'
        ], 404);
    }

    private function getCartTotal()
    {
        $cart = session()->get('cart', []);
        return array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);
        
    }
}
