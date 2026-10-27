@extends('layouts.app')

@section('content')
<h1>Finalizar Pedido (Checkout)</h1>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <form action="{{ route('checkout.store') }}" method="POST" style="background: #fff; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: 8px;">
        @csrf
        <h3>Datos de Facturación y Envío</h3>
        
        <div style="margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.25rem;">
            <label for="customer_name">Nombre completo:</label>
            <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
            @error('customer_name') <span style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.25rem;">
            <label for="customer_email">Correo electrónico:</label>
            <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email') }}" required>
            @error('customer_email') <span style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.25rem;">
            <label for="customer_phone">Teléfono:</label>
            <input type="text" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}">
            @error('customer_phone') <span style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.25rem;">
            <label for="shipping_address">Dirección de envío:</label>
            <textarea id="shipping_address" name="shipping_address" rows="3" required>{{ old('shipping_address') }}</textarea>
            @error('shipping_address') <span style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
        </div>

        <button type="submit">Generar Pedido y Pagar</button>
    </form>

    <div style="background: #fff; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: 8px; height: fit-content;">
        <h3>Resumen del Carrito</h3>
        <ul style="list-style: none; margin: 1rem 0; padding: 0;">
            @foreach ($items as $item)
                <li style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem;">
                    <span>{{ $item['quantity'] }}x {{ $item['product']->name }}</span>
                    <span>${{ number_format($item['line_total'], 2) }}</span>
                </li>
            @endforeach
        </ul>
        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 1rem 0;">
        <h4>Total: ${{ number_format($total, 2) }}</h4>
    </div>
</div>
@endsection