<?php

namespace App\Http\Controllers\Auth;

use App\Domains\Customers\Models\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerAuthController extends Controller
{
    /**
     * Mostrar formulario de login.
     */
    public function showLoginForm()
    {
        return view('auth.customer.login');
    }
    
    /**
     * Procesar login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('products.index'));
        }
        
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }
    
    /**
     * Mostrar formulario de registro.
     */
    public function showRegisterForm()
    {
        return view('auth.customer.register');
    }
    
    /**
     * Procesar registro.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => 'nullable|string|max:255',
        ]);
        
        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
        ]);
        
        Auth::guard('customer')->login($customer);
        
        return redirect()->route('products.index')
            ->with('success', '¡Cuenta creada exitosamente!');
    }
    
    /**
     * Cerrar sesión.
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('products.index');
    }
}

