<?php

namespace App\Services;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use Illuminate\Support\Facades\Session;

/**
 * Service para manejar el carrito de compras.
 * 
 * Usa sesión de Laravel (compatible con Redis cuando esté configurado).
 * El carrito se almacena en la sesión como un array de items.
 */
class CartService
{
    protected const CART_KEY = 'cart';
    
    /**
     * Obtener todos los items del carrito.
     */
    public function getItems(): array
    {
        return Session::get(self::CART_KEY, []);
    }
    
    /**
     * Agregar un producto al carrito.
     * 
     * @param Product $product
     * @param int $quantity
     * @param ProductVariant|null $variant
     * @return array Item agregado
     */
    public function add(Product $product, int $quantity = 1, ?ProductVariant $variant = null): array
    {
        $cart = $this->getItems();
        
        // Calcular precio (variante o producto)
        // Si hay variante y tiene precio, usar ese; si no, usar precio del producto
        $price = ($variant && $variant->price) ? $variant->price : $product->price;
        
        // Generar ID único del item (producto + variante)
        $itemId = $this->generateItemId($product->id, $variant?->id);
        
        // Verificar si el item ya existe
        if (isset($cart[$itemId])) {
            // Incrementar cantidad
            $cart[$itemId]['quantity'] += $quantity;
        } else {
            // Crear nuevo item
            $cart[$itemId] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $quantity,
                'price' => (float) $price,
                'name' => $product->name . ($variant ? ' - ' . $this->getVariantName($variant) : ''),
                'image' => $product->image_url ?? $variant?->image_path ?? null,
            ];
        }
        
        // Validar stock disponible
        $this->validateStock($cart[$itemId], $product, $variant);
        
        Session::put(self::CART_KEY, $cart);
        
        return $cart[$itemId];
    }
    
    /**
     * Actualizar cantidad de un item.
     */
    public function update(string $itemId, int $quantity): bool
    {
        $cart = $this->getItems();
        
        if (!isset($cart[$itemId])) {
            return false;
        }
        
        if ($quantity <= 0) {
            return $this->remove($itemId);
        }
        
        $cart[$itemId]['quantity'] = $quantity;
        
        // Validar stock
        $item = $cart[$itemId];
        $product = Product::find($item['product_id']);
        $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;
        
        $this->validateStock($cart[$itemId], $product, $variant);
        
        Session::put(self::CART_KEY, $cart);
        
        return true;
    }
    
    /**
     * Remover un item del carrito.
     */
    public function remove(string $itemId): bool
    {
        $cart = $this->getItems();
        
        if (!isset($cart[$itemId])) {
            return false;
        }
        
        unset($cart[$itemId]);
        
        Session::put(self::CART_KEY, $cart);
        
        return true;
    }
    
    /**
     * Limpiar todo el carrito.
     */
    public function clear(): void
    {
        Session::forget(self::CART_KEY);
    }
    
    /**
     * Obtener el total de items en el carrito.
     */
    public function getTotalItems(): int
    {
        return collect($this->getItems())->sum('quantity');
    }
    
    /**
     * Calcular el subtotal del carrito.
     */
    public function getSubtotal(): float
    {
        return collect($this->getItems())->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });
    }
    
    /**
     * Obtener items con productos cargados.
     */
    public function getItemsWithProducts(): array
    {
        $items = $this->getItems();
        $result = [];
        
        foreach ($items as $itemId => $item) {
            $product = Product::find($item['product_id']);
            $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;
            
            if (!$product) {
                continue; // Producto eliminado, saltar
            }
            
            $result[$itemId] = [
                'id' => $itemId,
                'product' => $product,
                'variant' => $variant,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['quantity'] * $item['price'],
                'name' => $item['name'],
                'image' => $item['image'] ?? $product->image_url,
            ];
        }
        
        return $result;
    }
    
    /**
     * Generar ID único para un item del carrito.
     */
    protected function generateItemId(int $productId, ?int $variantId = null): string
    {
        return $variantId 
            ? "product_{$productId}_variant_{$variantId}"
            : "product_{$productId}";
    }
    
    /**
     * Obtener nombre descriptivo de la variante.
     */
    protected function getVariantName(ProductVariant $variant): string
    {
        // Obtener atributos de la variante
        $attributes = $variant->attributes()->withPivot('value')->get();
        
        if ($attributes->isEmpty()) {
            return "Variante #{$variant->id}";
        }
        
        return $attributes->pluck('pivot.value')->join(', ');
    }
    
    /**
     * Validar stock disponible.
     */
    protected function validateStock(array &$item, Product $product, ?ProductVariant $variant): void
    {
        $availableStock = $variant?->stock ?? $product->stock;
        
        if ($item['quantity'] > $availableStock) {
            $item['quantity'] = max(0, $availableStock);
            
            if ($item['quantity'] == 0) {
                throw new \Exception("No hay stock disponible para {$item['name']}");
            }
        }
    }
}

