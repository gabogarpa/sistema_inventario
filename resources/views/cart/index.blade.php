@extends('layouts.app')

@section('content')
<h1>Tu Carrito de Compras</h1>

@if(empty($items) || count($items) === 0)
    <p>El carrito está vacío. <a href="{{ route('catalog.index') }}">Volver al catálogo</a></p>
@else
    <div class="cart-table-wrapper">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
            <thead>
                <tr style="border-bottom: 2px solid #e2e8f0; text-align: left;">
                    <th style="padding: 0.75rem;">Producto</th>
                    <th style="padding: 0.75rem;">Precio Unitario</th>
                    <th style="padding: 0.75rem;">Cantidad</th>
                    <th style="padding: 0.75rem;">Subtotal</th>
                    <th style="padding: 0.75rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 0.75rem;">{{ $item['product']->name }}</td>
                        <td style="padding: 0.75rem;">${{ number_format($item['product']->price, 2) }}</td>
                        <td style="padding: 0.75rem;">
                            <form action="{{ route('cart.update', $item['product']) }}" method="POST" style="display: inline-flex; gap: 0.5rem;">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $item['product']->stock }}" style="width: 60px;">
                                <button type="submit" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Actualizar</button>
                            </form>
                        </td>
                        <td style="padding: 0.75rem;">${{ number_format($item['line_total'], 2) }}</td>
                        <td style="padding: 0.75rem;">
                            <form action="{{ route('cart.destroy', $item['product']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: #dc2626; padding: 0.25rem 0.5rem; font-size: 0.85rem;">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: 8px;">
        <h3>Total a pagar: ${{ number_format($total, 2) }}</h3>
        <div style="display: flex; gap: 1rem;">
            <form action="{{ route('cart.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" style="background: #64748b;">Vaciar carrito</button>
            </form>
            @if (Route::has('checkout.create'))
                <a href="{{ route('checkout.create') }}" class="btn" style="text-decoration: none; display: inline-block; line-height: normal; padding: 0.5rem 1rem;">Proceder al Checkout</a>
            @endif
        </div>
    </div>
@endif
@endsection