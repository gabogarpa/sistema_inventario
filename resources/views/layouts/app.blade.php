<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Nómada Store') }}</title>
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="{{ route('catalog.index') }}" class="brand">{{ config('app.name', 'Nómada Store') }}</a>
            <nav>
                <a href="{{ route('catalog.index') }}">Catálogo</a>
                @if (Route::has('cart.index'))
                    <a href="{{ route('cart.index') }}">
                        Carrito ({{ collect(session('cart', []))->sum('quantity') }})
                    </a>
                @endif
            </nav>
        </div>
    </header>

    <main class="container">
        @if (session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>