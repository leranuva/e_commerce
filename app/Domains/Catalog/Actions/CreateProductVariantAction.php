<?php

namespace App\Domains\Catalog\Actions;

use App\Actions\Action;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

/**
 * Action para crear una variante de producto.
 * 
 * Maneja la creación de variantes con sus atributos (talla, color, material).
 */
class CreateProductVariantAction extends Action
{
    /**
     * Crea una nueva variante de producto.
     * 
     * @param Product $product
     * @param array $data Datos de la variante
     * @param array $attributes Atributos de la variante (ej: ['size' => 'XL', 'color' => 'Rojo'])
     * @return ProductVariant
     */
    public function execute(Product $product, array $data, array $attributes = []): ProductVariant
    {
        return DB::transaction(function () use ($product, $data, $attributes) {
            // Generar SKU si no se proporciona
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSku($product, $attributes);
            }

            // Crear la variante
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $data['sku'],
                'price' => $data['price'] ?? null,
                'stock' => $data['stock'] ?? 0,
                'image_path' => $data['image_path'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Asociar atributos
            if (!empty($attributes)) {
                $this->attachAttributes($variant, $attributes);
            }

            return $variant->load('attributes');
        });
    }

    /**
     * Genera un SKU único para la variante.
     */
    private function generateSku(Product $product, array $attributes): string
    {
        $skuParts = [$product->sku];
        
        foreach ($attributes as $attributeName => $value) {
            $skuParts[] = strtoupper(substr($value, 0, 3));
        }

        $baseSku = implode('-', $skuParts);
        $sku = $baseSku;
        $counter = 1;

        // Asegurar que el SKU sea único
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $baseSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    /**
     * Asocia atributos a la variante.
     */
    private function attachAttributes(ProductVariant $variant, array $attributes): void
    {
        foreach ($attributes as $attributeName => $value) {
            // Buscar el atributo por nombre
            $attribute = \App\Domains\Catalog\Models\Attribute::where('name', $attributeName)->first();
            
            if ($attribute) {
                $variant->attributes()->attach($attribute->id, ['value' => $value]);
            }
        }
    }
}

