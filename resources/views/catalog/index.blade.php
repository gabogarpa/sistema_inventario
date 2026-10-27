{{-- resources/views/catalog/index.blade.php --}}
@extends('layouts.app')

@section('content')
<h1>Productos</h1>
<div class="product-grid">
@foreach ($products as $product)
  <article>
    {{-- Si registraste una imagen en el Paso 5, puedes mostrarla aquí: --}}
    <img src="{{ asset('storage/'.$product->image) }}"
         alt="{{ $product->name }}">
    <h2>{{ $product->name }}</h2>
    <p>${{ number_format((float) $product->price, 2) }}</p>
    <a href="{{ route('catalog.show', ['product' => $product->id]) }}">Ver producto</a>
  </article>
@endforeach
</div>

{{ $products->links() }}
@endsection