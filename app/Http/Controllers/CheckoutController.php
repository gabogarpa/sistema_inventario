<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\WompiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function create()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'El carrito está vacío.');
        }

        $products = Product::whereIn('id', array_keys($cart))->get();
        $items = $products->map(fn($product) => [
            'product' => $product,
            'quantity' => min($cart[$product->id]['quantity'], $product->stock),
            'line_total' => round((float) $product->price * $cart[$product->id]['quantity'], 2),
        ]);

        $total = round($items->sum('line_total'), 2);

        return view('checkout.create', compact('items', 'total'));
    }

    public function store(StoreCheckoutRequest $request, WompiService $wompi)
    {
        $cart = session('cart', []);
        abort_if(empty($cart), 422, 'El carrito está vacío.');

        $order = DB::transaction(function () use ($cart, $request) {
            $products = Product::whereIn('id', array_keys($cart))
                ->where('is_active', true)->lockForUpdate()->get()->keyBy('id');
            
            $lines = [];
            foreach ($cart as $productId => $row) {
                $product = $products->get($productId);
                $qty = (int) $row['quantity'];
                
                abort_if(!$product || $qty < 1 || $qty > $product->stock, 422, 'El inventario cambió. Revise el carrito.');
                
                $lines[] = [$product, $qty, round((float) $product->price * $qty, 2)];
            }

            $total = round(collect($lines)->sum(fn ($line) => $line[2]), 2);

            $order = Order::create([
                'reference' => (string) Str::uuid(),
                ...$request->validated(),
                'subtotal' => $total, 
                'total' => $total, 
                'status' => 'pending',
            ]);

            foreach ($lines as [$product, $qty, $lineTotal]) {
                $order->items()->create([
                    'product_id' => $product->id, 
                    'name' => $product->name,
                    'unit_price' => $product->price, 
                    'quantity' => $qty,
                    'total' => $lineTotal,
                ]);
                $product->decrement('stock', $qty);
            }

            return $order;
        }, attempts: 3);

        // Si estamos en modo demostración (sin credenciales Wompi reales)
        if (config('services.wompi.demo_mode', true) && !$wompi->configured()) {
            session()->forget('cart');
            return redirect()->route('payment.result', $order);
        }

        $payment = $wompi->createPaymentLink($order);
        session()->forget('cart');

        return redirect()->away($payment['urlEnlace']);
    }
}