@extends('layouts.app')

@section('content')
<div class="product-detail">
    @if ($product->image)
        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
    @endif

    <div class="product-info">
        <p class="category">{{ $product->category->name }}</p>
        <h1>{{ $product->name }}</h1>
        <p class="price">${{ number_format((float) $product->price, 2) }}</p>
        <p>{{ $product->description }}</p>
        <p>Disponibles en stock: <strong>{{ $product->stock }}</strong></p>

        @if (Route::has('cart.store'))
            <form action="{{ route('cart.store', $product) }}" method="POST">
                @csrf
                <label for="quantity">Cantidad:</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ min($product->stock, 20) }}" required>
                <button type="submit">Agregar al carrito</button>
            </form>
        @endif

        <p><a href="{{ route('catalog.index') }}">&larr; Volver al catálogo</a></p>
    </div>
</div>
@endsection