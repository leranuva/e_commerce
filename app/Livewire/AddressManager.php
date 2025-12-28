<?php

namespace App\Livewire;

use App\Domains\Customers\Actions\SetDefaultAddressAction;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerAddress;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AddressManager extends Component
{
    public Customer $customer;
    public $addresses = [];
    public $showForm = false;
    public $editingAddress = null;

    // Form fields
    public $street = '';
    public $city = '';
    public $state = '';
    public $postal_code = '';
    public $country = 'México';
    public $is_default = false;

    public function mount()
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login');
        }

        $this->customer = Auth::guard('customer')->user();
        $this->loadAddresses();
    }

    /**
     * Cargar direcciones del cliente.
     */
    public function loadAddresses()
    {
        $this->addresses = $this->customer->addresses()->orderBy('is_default', 'desc')->get()->toArray();
    }

    /**
     * Mostrar formulario para nueva dirección.
     */
    public function showAddForm()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingAddress = null;
    }

    /**
     * Mostrar formulario para editar dirección.
     */
    public function editAddress($addressId)
    {
        $address = CustomerAddress::findOrFail($addressId);
        
        if ($address->customer_id !== $this->customer->id) {
            session()->flash('error', 'No tienes permiso para editar esta dirección');
            return;
        }

        $this->editingAddress = $address->id;
        $this->street = $address->street;
        $this->city = $address->city;
        $this->state = $address->state;
        $this->postal_code = $address->postal_code;
        $this->country = $address->country;
        $this->is_default = $address->is_default;
        $this->showForm = true;
    }

    /**
     * Guardar dirección (crear o actualizar).
     */
    public function saveAddress()
    {
        $this->validate([
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        if ($this->editingAddress) {
            // Actualizar dirección existente
            $address = CustomerAddress::findOrFail($this->editingAddress);
            $address->update([
                'street' => $this->street,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
            ]);

            // Si se marca como predeterminada, usar la Action
            if ($this->is_default && !$address->is_default) {
                SetDefaultAddressAction::run($this->customer, $address);
            }
        } else {
            // Crear nueva dirección
            $address = CustomerAddress::create([
                'customer_id' => $this->customer->id,
                'street' => $this->street,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
                'is_default' => false, // Se establecerá después si es necesario
            ]);

            // Si se marca como predeterminada, usar la Action
            if ($this->is_default) {
                SetDefaultAddressAction::run($this->customer, $address);
            }
        }

        $this->resetForm();
        $this->loadAddresses();
        session()->flash('message', $this->editingAddress ? 'Dirección actualizada' : 'Dirección agregada');
    }

    /**
     * Establecer dirección como predeterminada.
     */
    public function setDefault($addressId)
    {
        $address = CustomerAddress::findOrFail($addressId);
        
        if ($address->customer_id !== $this->customer->id) {
            session()->flash('error', 'No tienes permiso para modificar esta dirección');
            return;
        }

        SetDefaultAddressAction::run($this->customer, $address);
        $this->loadAddresses();
        session()->flash('message', 'Dirección predeterminada actualizada');
    }

    /**
     * Eliminar dirección.
     */
    public function deleteAddress($addressId)
    {
        $address = CustomerAddress::findOrFail($addressId);
        
        if ($address->customer_id !== $this->customer->id) {
            session()->flash('error', 'No tienes permiso para eliminar esta dirección');
            return;
        }

        $address->delete();
        $this->loadAddresses();
        session()->flash('message', 'Dirección eliminada');
    }

    /**
     * Cancelar edición/creación.
     */
    public function cancel()
    {
        $this->resetForm();
    }

    /**
     * Resetear formulario.
     */
    private function resetForm()
    {
        $this->showForm = false;
        $this->editingAddress = null;
        $this->street = '';
        $this->city = '';
        $this->state = '';
        $this->postal_code = '';
        $this->country = 'México';
        $this->is_default = false;
    }

    public function render()
    {
        return view('livewire.address-manager');
    }
}

