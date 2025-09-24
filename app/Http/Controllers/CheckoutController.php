<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Shift; // добавил импорт модели Shift
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = session()->get('cart', []);
        $total = $this->calculateTotal($cartItems);

        return view('checkout.index', compact('cartItems', 'total'));
    }

    public function process(Request $request)
    {
        // Валидация данных
        $validationRules = [
            'payment_method' => 'required|in:cash,card',
            'delivery_method' => 'required|in:pickup,delivery',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
        ];

        if ($request->delivery_method === 'delivery') {
            $validationRules['delivery_address'] = 'required|string|max:255';
        }

        $request->validate($validationRules, [
            'payment_method.required' => 'Выберите способ оплаты',
            'delivery_method.required' => 'Выберите способ получения',
            'delivery_address.required' => 'Укажите адрес доставки',
            'phone.required' => 'Укажите номер телефона',
        ]);

        // Проверяем, есть ли товары в корзине
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Ваша корзина пуста');
        }

        $unavailableProducts = [];
        foreach ($cart as $id => $item) {
            $product = Product::with('ingredients')->find($id);
            if ($product) {
                // Проверяем, можем ли мы приготовить нужное количество товара
                for ($i = 0; $i < $item['quantity']; $i++) {
                    if (!$product->isAvailable()) {
                        $unavailableProducts[] = $product->name_product;
                        break;
                    }
                }
            }
        }

        if (!empty($unavailableProducts)) {
            $errorMessage = 'К сожалению, следующие блюда временно недоступны из-за нехватки ингредиентов: ' . implode(', ', array_unique($unavailableProducts));
            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }

        DB::beginTransaction();
        try {
            $activeShift = Shift::where('status', 'active')->first();
            
            // Создаем новый заказ
            $order = new Order();
            $order->user_id = Auth::id();
            $order->shift_id = $activeShift ? $activeShift->id : null; // привязываем к активной смене
            $order->total_amount = $this->calculateTotal($cart);
            $order->status = 'В обработке';
            $order->payment_method = $request->payment_method;
            $order->delivery_method = $request->delivery_method;
            $order->delivery_address = $request->delivery_method === 'delivery' ? $request->delivery_address : null;
            $order->phone = $request->phone;
            $order->notes = $request->notes;
            $order->save();

            // Создаем элементы заказа и списываем ингредиенты
            foreach ($cart as $id => $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $id;
                $orderItem->quantity = $item['quantity'];
                $orderItem->price = $item['price'];
                $orderItem->save();

                // Списываем ингредиенты для каждого товара
                $product = Product::find($id);
                if ($product) {
                    for ($i = 0; $i < $item['quantity']; $i++) {
                        $product->reduceIngredients();
                    }
                }
            }

            DB::commit();

            // Очищаем корзину
            session()->forget('cart');

            // Перенаправляем на страницу подтверждения
            return redirect()->route('checkout.confirmation', ['order' => $order->id])
                ->with('success', 'Заказ успешно оформлен!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Произошла ошибка при оформлении заказа. Попробуйте еще раз.');
        }
    }

    private function calculateTotal($cart)
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function confirmation(Order $order)
    {
        // Проверяем, принадлежит ли заказ текущему пользователю
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        return view('checkout.confirmation', compact('order'));
    }
}
