<?php

namespace App\Domains\Customers\Actions;

use App\Actions\Action;
use App\Domains\Customers\Models\Customer;
use Illuminate\Support\Facades\Hash;

/**
 * Action para actualizar el perfil de un cliente.
 * 
 * Maneja la actualización de datos del cliente incluyendo
 * validaciones y encriptación de contraseñas.
 */
class UpdateCustomerProfileAction extends Action
{
    /**
     * Actualiza el perfil del cliente.
     * 
     * @param Customer $customer
     * @param array $data Datos a actualizar
     * @return Customer
     */
    public function execute(Customer $customer, array $data): Customer
    {
        // Si se proporciona una nueva contraseña, encriptarla
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Actualizar el cliente
        $customer->update($data);

        // Actualizar dirección si se proporciona
        if (isset($data['address'])) {
            $customer->addresses()->updateOrCreate(
                ['is_default' => true],
                $data['address']
            );
        }

        return $customer->fresh(['addresses']);
    }
}

