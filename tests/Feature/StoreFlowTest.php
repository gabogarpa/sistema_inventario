<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $category = Category::create([
        'name' => 'Accesorios',
        'slug' => 'accesorios',
    ]);

    $this->product = Product::create([
        'category_id' => $category->id,
        'name' => 'Mochila urbana',
        'slug' => 'mochila-urbana',
        'description' => 'Mochila para uso diario',
        'price' => 25.00,
        'stock' => 10,
        'is_active' => true,
    ]);
});

it('agrega, actualiza y elimina productos del carrito', function () {
    $this->post(route('cart.store', $this->product), ['quantity' => 2])
        ->assertRedirect(route('cart.index'));

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('$50.00');

    $this->patch(route('cart.update', $this->product), ['quantity' => 3])
        ->assertRedirect(route('cart.index'));

    $this->delete(route('cart.destroy', $this->product))
        ->assertRedirect(route('cart.index'));
});

it('crea un pedido y completa el pago demostrativo', function () {
    $this->withSession([
        'cart' => [
            $this->product->id => ['quantity' => 2],
        ],
    ])->post(route('checkout.store'), [
        'customer_name' => 'Ana Pérez',
        'customer_email' => 'ana@example.com',
        'customer_phone' => '7000-0000',
        'shipping_address' => 'San Salvador',
    ])->assertRedirect();

    $order = Order::firstOrFail();
    
    $this->post(route('payment.demo', $order))
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('paid');
});