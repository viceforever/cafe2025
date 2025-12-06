<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Shift;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = session()->get('cart', []);
        
        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'Ваша корзина пуста. Добавьте товары перед оформлением заказа.');
        }
        
        $total = $this->calculateTotal($cartItems);
        
        $activeShift = Shift::where('status', 'active')->first();
        $hasActiveShift = !is_null($activeShift);

        return view('checkout.index', compact('cartItems', 'total', 'hasActiveShift'));
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
            $validationRules['delivery_city'] = 'required|string|in:Иркутск';
            $validationRules['delivery_street'] = 'required|string|max:255';
            $validationRules['delivery_house'] = 'required|string|max:10';
            $validationRules['delivery_apartment'] = 'nullable|string|max:10';
        }

        $request->validate($validationRules, [
            'payment_method.required' => 'Выберите способ оплаты',
            'delivery_method.required' => 'Выберите способ получения',
            'delivery_city.required' => 'Укажите город доставки',
            'delivery_city.in' => 'Доставка осуществляется только по городу Иркутск',
            'delivery_street.required' => 'Укажите улицу или микрорайон',
            'delivery_house.required' => 'Укажите номер дома',
            'phone.required' => 'Укажите номер телефона',
        ]);

        // Проверяем, есть ли товары в корзине
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Ваша корзина пуста');
        }

        $productIds = array_keys($cart);
        
        // Используем транзакцию с блокировками для предотвращения race condition
        DB::beginTransaction();
        try {
            // Блокируем строки при загрузке продуктов для предотвращения одновременного доступа
            $products = Product::with(['ingredients' => function($query) {
                $query->lockForUpdate();
            }])->whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            // Оптимизированная проверка доступности - используем isAvailableInQuantity вместо цикла
            $unavailableProducts = [];
            foreach ($cart as $id => $item) {
                $product = $products->get($id);
                if ($product && !$product->isAvailableInQuantity($item['quantity'])) {
                    $unavailableProducts[] = $product->name_product;
                }
            }

            if (!empty($unavailableProducts)) {
                DB::rollBack();
                $errorMessage = 'К сожалению, следующие блюда временно недоступны из-за нехватки ингредиентов: ' . implode(', ', array_unique($unavailableProducts));
                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }

            $activeShift = Shift::where('status', 'active')->first();
            
            if (!$activeShift) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'В данный момент нет активной смены. Пожалуйста, попробуйте позже.');
            }
            
            // Создаем новый заказ
            $order = new Order();
            $order->user_id = Auth::id();
            $order->shift_id = $activeShift->id;
            $order->total_amount = $this->calculateTotal($cart);
            $order->status = OrderStatus::PENDING;
            $order->payment_method = $request->payment_method;
            $order->delivery_method = $request->delivery_method;
            
            if ($request->delivery_method === 'delivery') {
                $addressParts = [
                    'г. ' . $request->delivery_city,
                    $request->delivery_street,
                    'д. ' . $request->delivery_house
                ];
                
                if ($request->delivery_apartment) {
                    $addressParts[] = 'кв. ' . $request->delivery_apartment;
                }
                
                $order->delivery_address = implode(', ', $addressParts);
            } else {
                $order->delivery_address = null;
            }
            
            $order->phone = $request->phone;
            $order->notes = $request->notes;
            $order->save();

            // Создаем элементы заказа
            foreach ($cart as $id => $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $id;
                $orderItem->quantity = $item['quantity'];
                $orderItem->price = $item['price'];
                $orderItem->save();
            }

            // Списываем ингредиенты с валидацией (внутри reduceIngredientsInQuantity)
            foreach ($cart as $id => $item) {
                $product = $products->get($id);
                if ($product) {
                    $product->reduceIngredientsInQuantity($item['quantity']);
                }
            }

            DB::commit();

            // Очищаем корзину
            session()->forget('cart');

            // Перенаправляем на страницу подтверждения
            return redirect()->route('checkout.confirmation', ['order' => $order->id])
                ->with('success', 'Заказ успешно оформлен!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при оформлении заказа', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
