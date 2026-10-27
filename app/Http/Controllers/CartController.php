<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get();
        
        $items = $products->map(function ($product) use ($cart) {
            $quantity = min($cart[$product->id]['quantity'], $product->stock);
            return [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => round((float) $product->price * $quantity, 2),
            ];
        });

        $total = round($items->sum('line_total'), 2);

        return view('cart.index', compact('items', 'total'));
    }

    public function store(Request $request, Product $product)
    {
        abort_unless($product->is_active && $product->stock > 0, 404);
        
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20']
        ]);

        $cart = session('cart', []);
        $current = $cart[$product->id]['quantity'] ?? 0;
        $quantity = min($current + $data['quantity'], $product->stock);
        
        $cart[$product->id] = ['quantity' => $quantity];
        session(['cart' => $cart]);

        return to_route('cart.index')->with('success', 'Producto agregado al carrito');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20']
        ]);

        $cart = session('cart', []);
        
        if (isset($cart[$product->id])) {
            $quantity = min($data['quantity'], $product->stock);
            $cart[$product->id]['quantity'] = $quantity;
            session(['cart' => $cart]);
        }

        return to_route('cart.index')->with('success', 'Carrito actualizado');
    }

    public function destroy(Product $product)
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return to_route('cart.index')->with('success', 'Producto eliminado del carrito');
    }

    public function clear()
    {
        session()->forget('cart');
        return to_route('cart.index')->with('success', 'Carrito vaciado');
    }
}