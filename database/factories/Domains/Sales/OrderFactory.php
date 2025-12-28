<?php

namespace Database\Factories\Domains\Sales;

use App\Domains\Customers\Models\Customer;
use App\Domains\Sales\Models\Order;
use App\Domains\Sales\States\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Sales\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 2000);
        $tax = $subtotal * 0.16; // 16% IVA
        $shipping = 50.00;
        $total = $subtotal + $tax + $shipping;

        return [
            'customer_id' => Customer::factory(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_cost' => $shipping,
            'total' => $total,
            'status' => OrderStatus::PENDING,
            'payment_intent_id' => null,
            'shipping_address' => $this->faker->streetAddress(),
            'shipping_city' => $this->faker->city(),
            'shipping_postal_code' => $this->faker->postcode(),
        ];
    }

    /**
     * Indicate that the order should be paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PAID,
            'payment_intent_id' => 'pi_' . $this->faker->bothify('????????????????????????'),
        ]);
    }

    /**
     * Indicate that the order should be shipped.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::SHIPPED,
            'payment_intent_id' => 'pi_' . $this->faker->bothify('????????????????????????'),
        ]);
    }

    /**
     * Indicate that the order should be delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::DELIVERED,
            'payment_intent_id' => 'pi_' . $this->faker->bothify('????????????????????????'),
        ]);
    }

    /**
     * Indicate that the order should be cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::CANCELLED,
        ]);
    }
}

