<?php

namespace App\Domains\Customers\Actions;

use App\Actions\Action;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;

/**
 * Action para establecer una dirección como predeterminada.
 * 
 * Asegura que solo una dirección sea predeterminada por cliente.
 */
class SetDefaultAddressAction extends Action
{
    /**
     * Establece una dirección como predeterminada.
     * 
     * @param Customer $customer
     * @param CustomerAddress $address
     * @return CustomerAddress
     */
    public function execute(Customer $customer, CustomerAddress $address): CustomerAddress
    {
        // Verificar que la dirección pertenezca al cliente
        if ($address->customer_id !== $customer->id) {
            throw new \InvalidArgumentException('La dirección no pertenece al cliente');
        }

        return DB::transaction(function () use ($customer, $address) {
            // Quitar el flag de predeterminada de todas las direcciones del cliente
            $customer->addresses()->update(['is_default' => false]);

            // Establecer esta dirección como predeterminada
            $address->update(['is_default' => true]);

            return $address->fresh();
        });
    }
}

