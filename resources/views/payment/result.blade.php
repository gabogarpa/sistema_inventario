@extends('layouts.app')

@section('content')
<div style="background: #fff; padding: 2rem; border: 1px solid #e2e8f0; border-radius: 8px; max-width: 600px; margin: 0 auto;">
    <h1>Resultado del Pedido</h1>
    
    <p><strong>Referencia:</strong> {{ $order->reference }}</p>
    <p><strong>Estado del pedido:</strong> 
        <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: bold; background: {{ $order->status === 'paid' ? '#dcfce7' : '#fef9c3' }}; color: {{ $order->status === 'paid' ? '#166534' : '#854d0e' }};">
            {{ strtoupper($order->status) }}
        </span>
    </p>
    <p><strong>Cliente:</strong> {{ $order->customer_name }}</p>
    <p><strong>Total:</strong> ${{ number_format($order->total, 2) }}</p>

    <h3 style="margin-top: 1.5rem;">Artículos comprados:</h3>
    <ul style="margin-bottom: 1.5rem;">
        @foreach ($order->items as $item)
            <li>{{ $item->quantity }}x {{ $item->name }} - ${{ number_format($item->total, 2) }}</li>
        @endforeach
    </ul>

    @if ($order->status === 'pending')
        <div style="background: #f8fafc; padding: 1rem; border: 1px dashed #cbd5e1; border-radius: 6px; margin-bottom: 1.5rem;">
            <p>Este pedido está pendiente de pago. Como estás usando el modo demostrativo de la clase, puedes aprobarlo haciendo clic aquí:</p>
            <form action="{{ route('payment.demo', $order) }}" method="POST" style="margin-top: 0.75rem;">
                @csrf
                <button type="submit" style="background: #16a34a;">Simular Pago Exitoso (Demo)</button>
            </form>
        </div>
    @endif

    <a href="{{ route('catalog.index') }}" class="btn">Volver al Catálogo</a>
</div>
@endsection