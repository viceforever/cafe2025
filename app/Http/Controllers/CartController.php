<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class CartController extends Controller
{
    /**
     * Количество элементов корзины на странице
     */
    private const CART_ITEMS_PER_PAGE = 3;

    public function index(Request $request)
    {
        $cart = session()->get('cart', []);
        
        $unavailableItems = [];
        $productIds = array_keys($cart);
        if (!empty($productIds)) {
            $products = Product::with('ingredients')->whereIn('id', $productIds)->get()->keyBy('id');
            
            foreach ($cart as $id => $item) {
                $product = $products->get($id);
                if ($product && !$product->isAvailableInQuantity($item['quantity'])) {
                    $unavailableItems[] = $item['name'];
                }
            }
        }
        
        $perPage = self::CART_ITEMS_PER_PAGE;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $cartItems = collect($cart);
        
        $totalPages = ceil($cartItems->count() / $perPage);
        if ($currentPage > $totalPages && $totalPages > 0) {
            $currentPage = $totalPages;
        }
        
        $currentPageItems = $cartItems->slice(($currentPage - 1) * $perPage, $perPage)->all();
        
        $paginatedCart = new LengthAwarePaginator(
            $currentPageItems,
            $cartItems->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return view('cart.index', [
            'cart' => $cart,
            'paginatedCart' => $paginatedCart,
            'unavailableItems' => $unavailableItems
        ]);
    }

    public function add(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $quantity = intval($request->input('quantity', 1));

        $cart = session()->get('cart', []);
        $newQuantity = $quantity;
        if(isset($cart[$id])) {
            $newQuantity += $cart[$id]['quantity'];
        }

        $maxAvailable = $product->getMaxAvailableQuantity();
        
        if (!$product->isAvailableInQuantity($newQuantity)) {
            $errorMessage = $maxAvailable > 0 
                ? "К сожалению, на данный момент можно заказать не более {$maxAvailable} шт. этого товара."
                : "К сожалению, мы не можем приготовить больше количества данного блюда.";
                
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ]);
            }
            return redirect()->back()->with('error', $errorMessage);
        }
        
        if(isset($cart[$id])) {
            $cart[$id]['quantity'] += $quantity;
        } else {
            $cart[$id] = [
                "id" => $product->id,
                "name" => $product->name_product,
                "quantity" => $quantity,
                "price" => floatval($product->price_product),
                "img_product" => $product->img_product
            ];
        }
        
        session()->put('cart', $cart);
        
        // Если это AJAX запрос, возвращаем JSON
        if ($request->ajax() || $request->wantsJson()) {
            $cartCount = array_sum(array_column($cart, 'quantity'));
            $cartTotal = array_reduce($cart, function ($carry, $item) {
                return $carry + ($item['price'] * $item['quantity']);
            }, 0);
            
            return response()->json([
                'success' => true,
                'message' => 'Товар добавлен в корзину',
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
                'cart' => $cart
            ]);
        }
        
        return redirect()->back()->with('success', 'Товар добавлен в корзину!');
    }

    public function remove(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        if(isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        
        // Если это AJAX запрос, возвращаем JSON
        if ($request->ajax() || $request->wantsJson()) {
            $cartCount = array_sum(array_column($cart, 'quantity'));
            $cartTotal = array_reduce($cart, function ($carry, $item) {
                return $carry + ($item['price'] * $item['quantity']);
            }, 0);
            
            $perPage = self::CART_ITEMS_PER_PAGE;
            $currentPage = $request->input('page', 1);
            $totalItems = count($cart);
            $totalPages = ceil($totalItems / $perPage);
            
            if ($currentPage > $totalPages && $totalPages > 0) {
                $currentPage = $totalPages;
            }
            
            $cartItems = collect($cart);
            $currentPageItems = $cartItems->slice(($currentPage - 1) * $perPage, $perPage)->all();
            
            $paginatedCart = new LengthAwarePaginator(
                $currentPageItems,
                $cartItems->count(),
                $perPage,
                $currentPage,
                ['path' => route('cart.index'), 'query' => $request->query()]
            );
            
            $unavailableItems = [];
            $productIds = array_keys($cart);
            if (!empty($productIds)) {
                $products = Product::with('ingredients')->whereIn('id', $productIds)->get()->keyBy('id');
                
                foreach ($cart as $productId => $item) {
                    $product = $products->get($productId);
                    if ($product && !$product->isAvailableInQuantity($item['quantity'])) {
                        $unavailableItems[] = $item['name'];
                    }
                }
            }
            
            $itemsHtml = view('cart.partials.items', [
                'paginatedCart' => $paginatedCart
            ])->render();
            
            $paginationHtml = view('cart.partials.pagination', [
                'paginatedCart' => $paginatedCart
            ])->render();
            
            $needsRedirect = $currentPage != $request->input('page', 1);
            
            return response()->json([
                'success' => true,
                'message' => 'Товар удален из корзины',
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
                'cart' => $cart,
                'total_items' => $totalItems,
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'needs_redirect' => $needsRedirect,
                'redirect_page' => $currentPage,
                'items_html' => $itemsHtml,
                'pagination_html' => $paginationHtml,
                'has_unavailable_items' => !empty($unavailableItems)
            ]);
        }
        
        return redirect()->back()->with('success', 'Товар удален из корзины!');
    }

    public function update(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        if(isset($cart[$id])) {
            if($request->input('action') === 'increase') {
                $product = Product::with('ingredients')->find($id);
                if ($product && !$product->isAvailableInQuantity($cart[$id]['quantity'] + 1)) {
                    $maxAvailable = $product->getMaxAvailableQuantity();
                    $errorMessage = $maxAvailable > 0 
                        ? "На данный момент можно заказать не более {$maxAvailable} шт. этого товара."
                        : "К сожалению, мы не можем приготовить больше количества данного блюда.";
                        
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage
                        ]);
                    }
                    return redirect()->back()->with('error', $errorMessage);
                }
                $cart[$id]['quantity']++;
            } elseif($request->input('action') === 'decrease') {
                if($cart[$id]['quantity'] <= 1) {
                    unset($cart[$id]);
                } else {
                    $cart[$id]['quantity']--;
                }
            }
            session()->put('cart', $cart);
        }
        
        // Если это AJAX запрос, возвращаем JSON
        if ($request->ajax() || $request->wantsJson()) {
            $cartCount = array_sum(array_column($cart, 'quantity'));
            $cartTotal = array_reduce($cart, function ($carry, $item) {
                return $carry + ($item['price'] * $item['quantity']);
            }, 0);
            
            $itemTotal = isset($cart[$id]) ? $cart[$id]['price'] * $cart[$id]['quantity'] : 0;
            $itemQuantity = isset($cart[$id]) ? $cart[$id]['quantity'] : 0;
            
            $perPage = self::CART_ITEMS_PER_PAGE;
            $currentPage = $request->input('page', 1);
            $totalItems = count($cart);
            $totalPages = ceil($totalItems / $perPage);
            
            if ($currentPage > $totalPages && $totalPages > 0) {
                $currentPage = $totalPages;
            }
            
            $cartItems = collect($cart);
            $currentPageItems = $cartItems->slice(($currentPage - 1) * $perPage, $perPage)->all();
            
            $paginatedCart = new LengthAwarePaginator(
                $currentPageItems,
                $cartItems->count(),
                $perPage,
                $currentPage,
                ['path' => route('cart.index'), 'query' => $request->query()]
            );
            
            $unavailableItems = [];
            $productIds = array_keys($cart);
            if (!empty($productIds)) {
                $products = Product::with('ingredients')->whereIn('id', $productIds)->get()->keyBy('id');
                
                foreach ($cart as $productId => $item) {
                    $product = $products->get($productId);
                    if ($product && !$product->isAvailableInQuantity($item['quantity'])) {
                        $unavailableItems[] = $item['name'];
                    }
                }
            }
            
            $itemsHtml = view('cart.partials.items', [
                'paginatedCart' => $paginatedCart
            ])->render();
            
            $paginationHtml = view('cart.partials.pagination', [
                'paginatedCart' => $paginatedCart
            ])->render();
            
            $needsRedirect = $currentPage != $request->input('page', 1);
            
            return response()->json([
                'success' => true,
                'message' => 'Корзина обновлена',
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
                'item_total' => $itemTotal,
                'item_quantity' => $itemQuantity,
                'item_removed' => !isset($cart[$id]),
                'cart' => $cart,
                'total_items' => $totalItems,
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'needs_redirect' => $needsRedirect,
                'redirect_page' => $currentPage,
                'items_html' => $itemsHtml,
                'pagination_html' => $paginationHtml,
                'has_unavailable_items' => !empty($unavailableItems)
            ]);
        }
        
        return redirect()->back()->with('success', 'Корзина обновлена!');
    }
}
