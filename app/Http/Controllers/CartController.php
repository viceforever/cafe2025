<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = session()->get('cart', []);
        
        $perPage = 3;
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
            'paginatedCart' => $paginatedCart
        ]);
    }

    public function add(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $quantity = intval($request->input('quantity', 1));

        $cart = session()->get('cart', []);
        
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
            
            $perPage = 3;
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
                'items_html' => $itemsHtml, // Add items HTML to response
                'pagination_html' => $paginationHtml
            ]);
        }
        
        return redirect()->back()->with('success', 'Товар удален из корзины!');
    }

    public function update(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        if(isset($cart[$id])) {
            if($request->input('action') === 'increase') {
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
            
            $perPage = 3;
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
                'items_html' => $itemsHtml, // Add items HTML to response
                'pagination_html' => $paginationHtml
            ]);
        }
        
        return redirect()->back()->with('success', 'Корзина обновлена!');
    }
}
