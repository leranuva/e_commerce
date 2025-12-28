<?php

namespace App\Domains\Catalog\Actions;

use App\Actions\Action;
use App\Domains\Catalog\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Action para crear un nuevo producto.
 * 
 * Maneja la creación de productos incluyendo la gestión de imágenes,
 * validaciones y relaciones con categorías.
 */
class CreateProductAction extends Action
{
    /**
     * Crea un nuevo producto.
     * 
     * @param array $data Datos del producto
     * @return Product
     */
    public function execute(array $data): Product
    {
        // Manejar imagen si existe
        if (isset($data['image']) && $data['image']->isValid()) {
            $data['image_path'] = $data['image']->store('products', 'public');
        }

        // Generar slug si no se proporciona
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            // Asegurar que el slug sea único
            $originalSlug = $data['slug'];
            $counter = 1;
            while (Product::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Crear el producto
        $product = Product::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'sku' => $data['sku'],
            'price' => $data['price'],
            'stock' => $data['stock'] ?? 0,
            'category_id' => $data['category_id'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        // Asociar atributos si existen
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $product->attributes()->sync($data['attributes']);
        }

        return $product->load(['category', 'attributes']);
    }
}

