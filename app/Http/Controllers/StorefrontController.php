<?php

namespace App\Http\Controllers;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Category;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    /**
     * Mostrar lista de productos.
     */
    public function index(Request $request)
    {
        $query = Product::query()->where('is_active', true);
        
        // Filtro por categoría
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Búsqueda
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // Ordenamiento
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        $products = $query->with('category')->paginate(12);
        $categories = Category::where('is_active', true)->get();
        
        // Para la página de inicio, obtener productos destacados y categorías principales
        $featuredProducts = null;
        $mainCategories = null;
        
        if (request()->routeIs('home')) {
            // Productos destacados (más recientes o con mejor stock)
            $featuredProducts = Product::where('is_active', true)
                ->with(['category', 'media'])
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();
            
            // Categorías principales (las que tienen más productos)
            $mainCategories = Category::where('is_active', true)
                ->whereNull('parent_id')
                ->withCount('products')
                ->having('products_count', '>', 0)
                ->orderBy('products_count', 'desc')
                ->limit(4)
                ->get();
        }
        
        return view('storefront.products.index', compact('products', 'categories', 'featuredProducts', 'mainCategories'));
    }
    
    /**
     * Mostrar detalle de un producto.
     */
    public function show(Product $product)
    {
        // Cargar relaciones necesarias
        $product->load(['category', 'variants.attributes']);
        
        // Productos relacionados (misma categoría)
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();
        
        return view('storefront.products.show', compact('product', 'relatedProducts'));
    }
}

