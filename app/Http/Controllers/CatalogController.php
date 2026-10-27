<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class CatalogController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->latest()
            ->paginate(12);

        $categories = Category::whereHas('products', fn ($query) =>
            $query->where('is_active', true)->where('stock', '>', 0)
        )->orderBy('name')->get();

        return view('catalog.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active && $product->stock > 0, 404);
        $product->load('category');
        return view('catalog.show', compact('product'));
    }
}