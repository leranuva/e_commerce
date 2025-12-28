<?php

namespace Database\Seeders;

use App\Domains\Catalog\Models\Attribute;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerAddress;
use App\Domains\Sales\Models\Order;
use App\Domains\Sales\Models\OrderItem;
use App\Domains\Sales\States\OrderStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear Usuario Admin para Filament
        $this->call(AdminUserSeeder::class);

        // 2. Crear Categorías Principales (sin padre)
        $mainCategories = Category::factory()
            ->count(5)
            ->create();

        // 3. Crear Subcategorías para algunas categorías principales
        $mainCategories->take(3)->each(function ($category) {
            Category::factory()
                ->count(2)
                ->withParent($category)
                ->create();
        });

        // 4. Crear Atributos (Color, Talla, Material, etc.)
        $attributes = Attribute::factory()->count(5)->create();

        // 5. Crear Productos para cada categoría principal
        $mainCategories->each(function ($category) use ($attributes) {
            $products = Product::factory()
                ->count(10)
                ->forCategory($category)
                ->create()
                ->each(function ($product) use ($attributes) {
                    // Agregar imágenes de prueba usando Unsplash (más confiable)
                    try {
                        // Usar diferentes categorías de imágenes de Unsplash para variedad
                        $imageCategories = ['cosmetics', 'beauty', 'makeup', 'skincare', 'perfume', 'lipstick', 'foundation', 'mascara'];
                        $category = $imageCategories[array_rand($imageCategories)];
                        $imageUrl = "https://source.unsplash.com/640x480/?" . $category . "&sig=" . $product->id;
                        
                        $product->addMediaFromUrl($imageUrl)
                            ->toMediaCollection('images');
                    } catch (\Exception $e) {
                        // Si falla la descarga de imagen, usar placeholder local
                        try {
                            $product->addMediaFromUrl("https://via.placeholder.com/640x480/cccccc/666666?text=" . urlencode($product->name))
                                ->toMediaCollection('images');
                        } catch (\Exception $e2) {
                            // Si también falla, continuar sin imagen
                        }
                    }

                    // Crear variantes para algunos productos (30% de los productos)
                    if (rand(1, 10) <= 3) {
                        ProductVariant::factory()
                            ->count(rand(2, 5))
                            ->for($product)
                            ->create()
                            ->each(function ($variant) use ($attributes) {
                                // Asociar algunos atributos a las variantes
                                $selectedAttributes = $attributes->random(rand(1, 2));
                                foreach ($selectedAttributes as $attribute) {
                                    $variant->attributes()->attach($attribute->id, [
                                        'value' => $this->getAttributeValue($attribute->name),
                                    ]);
                                }
                            });
                    }
                });

            // Asociar algunos atributos a productos
            $products->random(5)->each(function ($product) use ($attributes) {
                $selectedAttributes = $attributes->random(rand(1, 3));
                foreach ($selectedAttributes as $attribute) {
                    $product->attributes()->attach($attribute->id, [
                        'value' => $this->getAttributeValue($attribute->name),
                    ]);
                }
            });
        });

        // 6. Crear Clientes Ficticios con Direcciones
        $customers = Customer::factory()
            ->count(20)
            ->create()
            ->each(function ($customer) {
                // Crear 1-3 direcciones por cliente
                $addressCount = rand(1, 3);
                CustomerAddress::factory()
                    ->count($addressCount)
                    ->for($customer)
                    ->create();

                // Marcar la primera dirección como predeterminada
                $customer->addresses()->first()?->update(['is_default' => true]);
            });

        // 7. Crear Órdenes con diferentes estados para probar el State Machine
        $customers->random(15)->each(function ($customer) {
            // Crear órdenes en diferentes estados
            $statuses = [
                OrderStatus::PENDING,
                OrderStatus::PAID,
                OrderStatus::SHIPPED,
                OrderStatus::DELIVERED,
                OrderStatus::CANCELLED,
            ];

            $status = $statuses[array_rand($statuses)];

            $order = Order::factory()
                ->for($customer)
                ->create([
                    'status' => $status,
                ]);

            // Crear items para cada orden (1-4 productos)
            $products = Product::where('is_active', true)
                ->inRandomOrder()
                ->limit(rand(1, 4))
                ->get();

            $subtotal = 0;
            foreach ($products as $product) {
                $quantity = rand(1, 3);
                $price = $product->price;
                $itemSubtotal = $quantity * $price;

                OrderItem::factory()
                    ->for($order)
                    ->for($product)
                    ->create([
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $itemSubtotal,
                    ]);

                $subtotal += $itemSubtotal;
            }

            // Recalcular totales de la orden
            $tax = $subtotal * 0.16; // 16% IVA
            $shipping = 50.00;
            $total = $subtotal + $tax + $shipping;

            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shipping,
                'total' => $total,
            ]);

            // Si la orden está pagada o más avanzada, agregar payment_intent_id
            if (in_array($status, [OrderStatus::PAID, OrderStatus::SHIPPED, OrderStatus::DELIVERED])) {
                $order->update([
                    'payment_intent_id' => 'pi_' . \Illuminate\Support\Str::random(24),
                ]);
            }
        });

        $this->command->info('✅ Base de datos poblada exitosamente!');
        $this->command->info('📦 Productos: ' . Product::count());
        $this->command->info('📁 Categorías: ' . Category::count());
        $this->command->info('👥 Clientes: ' . Customer::count());
        $this->command->info('🛒 Órdenes: ' . Order::count());
    }

    /**
     * Obtener un valor aleatorio para un atributo según su nombre.
     */
    private function getAttributeValue(string $attributeName): string
    {
        return match (strtolower($attributeName)) {
            'color' => fake()->randomElement(['Rojo', 'Azul', 'Verde', 'Negro', 'Blanco', 'Gris', 'Amarillo', 'Rosa']),
            'talla' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'material' => fake()->randomElement(['Algodón', 'Poliester', 'Lino', 'Seda', 'Cuero', 'Denim']),
            'tamaño' => fake()->randomElement(['Pequeño', 'Mediano', 'Grande', 'Extra Grande']),
            'estilo' => fake()->randomElement(['Casual', 'Formal', 'Deportivo', 'Vintage', 'Moderno']),
            default => fake()->word(),
        };
    }
}
