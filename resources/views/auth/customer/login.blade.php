@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="max-w-md mx-auto px-4 py-8">
    <div class="bg-white shadow rounded-lg p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Iniciar Sesión</h1>
        
        <form method="POST" action="{{ route('customer.login') }}" class="space-y-4">
            @csrf
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input 
                    type="email" 
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input 
                    type="password" 
                    id="password"
                    name="password"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="remember"
                    name="remember"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
                <label for="remember" class="ml-2 text-sm text-gray-700">
                    Recordarme
                </label>
            </div>
            
            <button 
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md"
            >
                Iniciar Sesión
            </button>
        </form>
        
        <p class="mt-4 text-center text-sm text-gray-600">
            ¿No tienes cuenta? 
            <a href="{{ route('customer.register') }}" class="text-blue-600 hover:text-blue-800">
                Regístrate aquí
            </a>
        </p>
    </div>
</div>
@endsection

