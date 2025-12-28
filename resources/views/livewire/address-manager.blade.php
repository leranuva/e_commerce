<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold">Mis Direcciones</h3>
        <button 
            wire:click="showAddForm"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
        >
            + Agregar Dirección
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if($showForm)
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h4 class="text-md font-semibold mb-4">
                {{ $editingAddress ? 'Editar Dirección' : 'Nueva Dirección' }}
            </h4>
            
            <form wire:submit.prevent="saveAddress" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Calle y Número</label>
                    <input 
                        type="text" 
                        wire:model="street"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    >
                    @error('street') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ciudad</label>
                        <input 
                            type="text" 
                            wire:model="city"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                        @error('city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <input 
                            type="text" 
                            wire:model="state"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('state') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Código Postal</label>
                        <input 
                            type="text" 
                            wire:model="postal_code"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                        @error('postal_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">País</label>
                        <input 
                            type="text" 
                            wire:model="country"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                        @error('country') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model="is_default"
                        id="is_default"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="is_default" class="ml-2 block text-sm text-gray-700">
                        Establecer como dirección predeterminada
                    </label>
                </div>

                <div class="flex gap-2">
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        Guardar
                    </button>
                    <button 
                        type="button"
                        wire:click="cancel"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="space-y-3">
        @forelse($addresses as $address)
            <div class="bg-white p-4 rounded-lg shadow border {{ $address['is_default'] ? 'border-blue-500' : '' }}">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        @if($address['is_default'])
                            <span class="inline-block px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded mb-2">
                                Predeterminada
                            </span>
                        @endif
                        <p class="font-medium">{{ $address['street'] }}</p>
                        <p class="text-sm text-gray-600">
                            {{ $address['city'] }}, {{ $address['state'] }} {{ $address['postal_code'] }}
                        </p>
                        <p class="text-sm text-gray-600">{{ $address['country'] }}</p>
                    </div>
                    <div class="flex gap-2">
                        @if(!$address['is_default'])
                            <button 
                                wire:click="setDefault({{ $address['id'] }})"
                                class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                            >
                                Establecer como predeterminada
                            </button>
                        @endif
                        <button 
                            wire:click="editAddress({{ $address['id'] }})"
                            class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                        >
                            Editar
                        </button>
                        <button 
                            wire:click="deleteAddress({{ $address['id'] }})"
                            wire:confirm="¿Estás seguro de eliminar esta dirección?"
                            class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                No tienes direcciones guardadas. Agrega una para comenzar.
            </div>
        @endforelse
    </div>
</div>

