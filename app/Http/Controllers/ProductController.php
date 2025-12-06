<?php
namespace App\Http\Controllers;
use App\Models\CategoryProduct;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        // Категории обычно немногочисленны, eager loading не требуется
        $categories = CategoryProduct::all();
        // Используем eager loading для категорий товаров
        $products = Product::with('category')->get()->groupBy('id_category');
        $totalProducts = Product::count();
        return view('products.index', compact('categories','products', 'totalProducts'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'nullable|string|max:255'
        ]);
        
        $query = $request->input('query');
        // Категории обычно немногочисленны, eager loading не требуется
        $categories = CategoryProduct::all();
        $totalProducts = Product::count();

        if ($query){
            // Используем eager loading для категорий
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
        $product = Product::with('ingredients')->findOrFail($id);
        $maxAvailableQuantity = $product->getMaxAvailableQuantity();
        
        $cart = session()->get('cart', []);
        $quantityInCart = isset($cart[$id]) ? $cart[$id]['quantity'] : 0;
        
        $availableToAdd = $maxAvailableQuantity - $quantityInCart;
        
        return view('products.product', compact('product', 'maxAvailableQuantity', 'quantityInCart', 'availableToAdd'));
    }

    public function checkQuantity(Request $request, $id)
    {
        $product = Product::with('ingredients')->findOrFail($id);
        $quantity = intval($request->input('quantity', 1));
        
        $cart = session()->get('cart', []);
        $quantityInCart = isset($cart[$id]) ? $cart[$id]['quantity'] : 0;
        $totalQuantity = $quantity + $quantityInCart; // Общее количество с учетом корзины
        
        $maxAvailable = $product->getMaxAvailableQuantity();
        
        if (!$product->isAvailableInQuantity($totalQuantity)) {
            $availableToAdd = max(0, $maxAvailable - $quantityInCart);
            $errorMessage = $availableToAdd > 0 
                ? "На данный момент в корзину можно добавить не более {$availableToAdd} шт. этого товара (в корзине: {$quantityInCart} шт.)."
                : "К сожалению, мы не можем приготовить больше количества данного блюда.";
                
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'maxAvailable' => $availableToAdd
            ]);
        }
        
        return response()->json([
            'success' => true,
            'maxAvailable' => $maxAvailable - $quantityInCart,
            'message' => 'Количество доступно'
        ]);
    }

    public function addToCart(Request $request,$id)
    {
        $product = Product::findOrFail($id);
        $quantity = $request->input('quantity', 1);
        
        if (!$product->isAvailableInQuantity($quantity)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'К сожалению, мы не можем приготовить больше количества данного блюда.'
                ], 400);
            }
            return redirect()->back()->with('error', 'К сожалению, мы не можем приготовить больше количества данного блюда.');
        }
        
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
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Товар добавлен в корзину!'
            ]);
        }
        
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
