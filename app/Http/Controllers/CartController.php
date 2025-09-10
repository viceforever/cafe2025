<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request,$id)
    {
        $product = Product::findOrFail($id);
        $quantity = intval($request->input('quantity', 1));

        $cart = session()->get('cart', []);
        
        if(isset($cart[$id])) {
            $cart[$id]['quantity']+= $quantity;
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
        return redirect()->back()->with([
            'success' => 'Товар добавлен в корзину!',
            'product_id' => $product->id,
        ]);
    }

    public function remove($id)
    {
        $cart = session()->get('cart', []);
        if(isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Товар удален из корзины!');
    }

    public function update(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        if(isset($cart[$id])) {
            if($request->input('action') === 'increase') {
                $cart[$id]['quantity']++;
            } elseif($request->input('action') === 'decrease' && $cart[$id]['quantity'] > 1) {
                $cart[$id]['quantity']--;
            }
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Корзина обновлена!');
    }
}